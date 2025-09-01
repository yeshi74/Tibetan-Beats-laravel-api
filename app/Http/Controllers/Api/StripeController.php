<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\BillingPortal\Session as BillingPortalSession;

class StripeController extends Controller
{
    /**
     * POST /api/stripe/checkout  (auth:sanctum)
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $request->validate([
                'plan' => 'required|string|in:monthly,yearly',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $secret = config('services.stripe.secret');
            if (!$secret) {
                return response()->json(['error' => 'Stripe secret not configured'], 500);
            }
            Stripe::setApiKey($secret);

            // Map frontend plan → Stripe price IDs
            $plan    = $request->input('plan', 'monthly');
            $priceId = $plan === 'yearly'
                ? config('services.stripe.price_yearly')
                : config('services.stripe.price_monthly');

            if (!$priceId) {
                return response()->json(['error' => 'Stripe price id not configured'], 500);
            }

            $session = CheckoutSession::create([
                'mode'  => 'subscription',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price'    => $priceId,
                    'quantity' => 1,
                ]],
                'client_reference_id' => (string) $user->id,
                'customer_email'      => $user->email,
                'metadata'            => [
                    'user_id' => (string) $user->id,
                    'plan'    => $plan,
                ],
                // 👇 Redirect back into your app using deep link
                'success_url' => 'tibetanmusic://premium/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => 'tibetanmusic://premium/cancel',
            ]);

            return response()->json(['url' => $session->url], 200);

        } catch (\Throwable $e) {
            Log::error('[Stripe] createCheckoutSession failed', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Could not create checkout session'], 500);
        }
    }

    /**
     * POST /api/stripe/confirm (auth:sanctum)
     * Called from Flutter after returning from Stripe Checkout
     */
    public function confirmAfterCheckout(Request $request)
    {
        $request->validate(['session_id' => 'required|string']);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::retrieve([
                'id'     => $request->string('session_id'),
                'expand' => ['subscription'],
            ]);

            $user = null;
            if (!empty($session->client_reference_id)) {
                $user = User::find((int) $session->client_reference_id);
            }
            if (!$user) {
                $user = $request->user();
            }
            if (!$user) {
                return response()->json(['error' => 'User not found for session'], 404);
            }

            $sub = $session->subscription; // may be \Stripe\Subscription|null
            $isActive = $sub && isset($sub->status) && in_array($sub->status, ['active','trialing'], true);

            if ($isActive) {
                if (!empty($session->customer)) {
                    $user->stripe_customer_id = $session->customer;
                }
                if ($sub && !empty($sub->id)) {
                    $user->stripe_subscription_id = $sub->id;
                }
                $user->is_premium = 1;
                $user->save();
            }

            return response()->json([
                'ok'         => true,
                'is_premium' => (bool) $user->is_premium,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Stripe] confirmAfterCheckout failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/stripe/webhook (no auth)
     */
    public function webhook(Request $request)
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $whSecret   = config('services.stripe.webhook_secret');

        try {
            $event = $whSecret
                ? Webhook::constructEvent($payload, $sigHeader, $whSecret)
                : json_decode($payload, false); // dev fallback
        } catch (\Throwable $e) {
            Log::warning('[Stripe] Webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'signature verification failed'], 400);
        }

        $type = $event->type ?? 'unknown';
        Log::info('[Stripe] Event received', ['type' => $type]);

        switch ($type) {
            case 'checkout.session.completed': {
                $session = $event->data->object;
                $user = null;

                if (!empty($session->client_reference_id)) {
                    $user = User::find((int) $session->client_reference_id);
                }
                if (!$user && !empty($session->customer_email)) {
                    $user = User::where('email', $session->customer_email)->first();
                }

                if ($user) {
                    $user->is_premium = 1;
                    if (!empty($session->customer)) {
                        $user->stripe_customer_id = $session->customer;
                    }
                    if (!empty($session->subscription)) {
                        $user->stripe_subscription_id = $session->subscription;
                    }
                    $user->save();
                    Log::info('[Stripe] User upgraded to premium via webhook', ['user_id' => $user->id]);
                }
                break;
            }

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted': {
                $sub = $event->data->object;
                $status = $sub->status ?? null;
                $customerId = $sub->customer ?? null;
                $user = $customerId ? User::where('stripe_customer_id', $customerId)->first() : null;

                if ($user) {
                    $active = ($type === 'customer.subscription.updated') &&
                              in_array($status, ['active', 'trialing'], true);
                    $user->is_premium = $active ? 1 : 0;
                    $user->stripe_subscription_id = $sub->id ?? $user->stripe_subscription_id;
                    $user->save();
                    Log::info('[Stripe] Subscription sync', [
                        'user_id' => $user->id,
                        'status'  => $status,
                    ]);
                }
                break;
            }

            default:
                Log::info('[Stripe] Unhandled event type', ['type' => $type]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * POST /api/stripe/billing-portal (auth:sanctum)
     */
    public function createBillingPortal(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $secret = config('services.stripe.secret');
            if (!$secret) {
                Log::error('[Stripe] Missing services.stripe.secret');
                return response()->json(['error' => 'Stripe secret not configured'], 500);
            }
            Stripe::setApiKey($secret);

            $returnUrl = 'tibetanmusic://premium/portalReturn';

            $customerId = $user->stripe_customer_id;
            if (!$customerId) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'name'  => $user->name ?? null,
                    'metadata' => [
                        'app_user_id' => (string) $user->id,
                    ],
                ]);
                $customerId = $customer->id;
                $user->stripe_customer_id = $customerId;
                $user->save();
            }

            $session = BillingPortalSession::create([
                'customer'   => $customerId,
                'return_url' => $returnUrl,
            ]);

            return response()->json(['url' => $session->url], 200);

        } catch (\Throwable $e) {
            Log::error('[Stripe] createBillingPortal failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
