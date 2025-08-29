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
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Stripe secret & price
            $secret  = config('services.stripe.secret');
            $priceId = config('services.stripe.price_id');   // set in config/services.php -> env('STRIPE_PRICE_ID')

            if (!$secret)  return response()->json(['error' => 'Stripe secret not configured'], 500);
            if (!$priceId) return response()->json(['error' => 'Stripe price id not configured'], 500);

            Stripe::setApiKey($secret);

            // Absolute URLs for redirect
            $host = $request->getSchemeAndHttpHost(); // e.g. http://172.20.10.3:8000

            $session = CheckoutSession::create([
                'mode'  => 'subscription',
                // PM types are inferred; you can omit, but keep if you want to force
                'payment_method_types' => ['card', 'link'],
                'line_items' => [[
                    'price'    => $priceId,
                    'quantity' => 1,
                ]],
                'client_reference_id' => (string) $user->id,
                'customer_email'      => $user->email,

                'success_url' => $host . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $host . '/checkout/cancel',
                // 'allow_promotion_codes' => true,
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
     * POST /api/stripe/webhook  (no auth)
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

                if (!$user) {
                    Log::warning('[Stripe] Could not map checkout session to user', [
                        'client_reference_id' => $session->client_reference_id ?? null,
                        'customer_email'      => $session->customer_email ?? null,
                    ]);
                    break;
                }

                $user->is_premium = 1;
                $user->save();
                Log::info('[Stripe] User upgraded to premium', ['user_id' => $user->id]);
                break;
            }

            case 'customer.subscription.updated': {
                $sub = $event->data->object;
                $status = $sub->status ?? null;
                // You generally don’t get customer_email here; to be robust, store user_id in metadata when creating the sub
                $customerEmail = $sub->customer_email ?? null;
                $user = $customerEmail ? User::where('email', $customerEmail)->first() : null;

                if ($user) {
                    $active = in_array($status, ['active', 'trialing'], true);
                    $user->is_premium = $active ? 1 : 0;
                    $user->save();
                    Log::info('[Stripe] Subscription updated', ['user_id' => $user->id, 'status' => $status]);
                }
                break;
            }

            case 'customer.subscription.deleted': {
                $sub = $event->data->object;
                $customerEmail = $sub->customer_email ?? null;
                $user = $customerEmail ? User::where('email', $customerEmail)->first() : null;

                if ($user) {
                    $user->is_premium = 0;
                    $user->save();
                    Log::info('[Stripe] Subscription deleted -> premium=0', ['user_id' => $user->id]);
                }
                break;
            }

            default:
                Log::info('[Stripe] Unhandled event type', ['type' => $type]);
        }

        return response()->json(['received' => true]);
    }


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

        // Use a deep link OR a simple web route that exists.
        // Deep link example: $returnUrl = 'tibetanmusic://premium/portalReturn';
        $returnUrl = $this->appBaseUrl($request) . '/billing/return';

        // ---- Ensure/lookup Customer safely ----
        $customerId = $user->stripe_customer_id;

        try {
            if ($customerId) {
                // Verify it exists (in case the id was from the other mode live/test)
                $cust = Customer::retrieve($customerId, []);
            } else {
                // Try to find existing by email to avoid duplicates
                $found = \Stripe\Customer::all(['email' => $user->email, 'limit' => 1]);
                if (count($found->data) > 0) {
                    $cust = $found->data[0];
                } else {
                    $cust = Customer::create([
                        'email' => $user->email,
                        'name'  => $user->name ?? null,
                        'metadata' => [
                            'app_user_id' => (string) $user->id,
                        ],
                    ]);
                }

                // Persist
                $user->stripe_customer_id = $cust->id;
                $user->save();
            }
        } catch (\Throwable $e) {
            Log::error('[Stripe] Customer ensure failed', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Could not create/find customer'], 500);
        }

        // ---- Create Billing Portal session ----
        try {
            $session = BillingPortalSession::create([
                'customer'   => $user->stripe_customer_id,
                'return_url' => $returnUrl,
                // 'configuration' => 'bpc_XXXX', // only if you use a custom portal config
            ]);
        } catch (\Throwable $e) {
            Log::error('[Stripe] Billing portal session create failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['url' => $session->url], 200);

    } catch (\Throwable $e) {
        Log::error('[Stripe] createBillingPortal fatal', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function appBaseUrl(Request $request): string
{
    $appUrl = config('app.url');
    if (!empty($appUrl)) {
        return rtrim($appUrl, '/');
    }
    return rtrim($request->getSchemeAndHttpHost(), '/');
}

public function confirmAfterCheckout(Request $request)
{
    $request->validate(['session_id' => 'required|string']);

    try {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Get latest session + subscription details
        $session = \Stripe\Checkout\Session::retrieve([
            'id'     => $request->string('session_id'),
            'expand' => ['subscription'],
        ]);

        // Prefer mapping via client_reference_id (we set this at checkout)
        $user = null;
        if (!empty($session->client_reference_id)) {
            $user = \App\Models\User::find((int) $session->client_reference_id);
        }
        // Fallback to currently authenticated user
        if (!$user) {
            $user = $request->user();
        }
        if (!$user) {
            return response()->json(['error' => 'User not found for session'], 404);
        }

        // If payment succeeded, persist customer/subscription + premium
        $paid = ($session->payment_status === 'paid');
        $sub  = $session->subscription; // may be \Stripe\Subscription|null
        $isActive = false;
        if ($sub && isset($sub->status)) {
            $isActive = in_array($sub->status, ['active','trialing'], true);
        }

        if ($paid) {
            if (!empty($session->customer)) {
                $user->stripe_customer_id = $session->customer;
            }
            if ($sub && !empty($sub->id)) {
                $user->stripe_subscription_id = $sub->id;
            }
            // If we have either paid or active sub, grant premium
            $user->is_premium = $isActive || $paid ? 1 : 0;
            $user->save();
        }

        return response()->json([
            'ok'         => true,
            'is_premium' => (bool) $user->is_premium,
        ]);
    } catch (\Throwable $e) {
        \Log::error('[Stripe] confirmAfterCheckout failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



}
