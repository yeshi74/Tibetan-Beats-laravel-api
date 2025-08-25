<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Create a Checkout session for a subscription.
     * Route: POST /api/stripe/checkout  (auth:sanctum)
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $user = $request->user(); // must be authenticated
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $priceId = config('services.stripe.price_id');
            if (!$priceId) {
                return response()->json(['error' => 'Stripe price id not configured'], 500);
            }

            $session = CheckoutSession::create([
                'mode' => 'subscription',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price'    => $priceId,
                    'quantity' => 1,
                ]],
                // identify the user so we can mark them premium in the webhook
                'client_reference_id' => (string) $user->id,
                'customer_email'      => $user->email,

                'success_url' => config('app.url') . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => config('app.url') . '/cancel',
            ]);

            return response()->json(['url' => $session->url], 200);
        } catch (\Throwable $e) {
            Log::error('[Stripe] createCheckoutSession failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stripe webhook endpoint.
     * Route: POST /api/stripe/webhook  (no auth)
     */
    public function webhook(Request $request)
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $whSecret   = config('services.stripe.webhook_secret');

        try {
            if ($whSecret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $whSecret);
            } else {
                // Fallback (NOT recommended for production)
                $event = json_decode($payload, false);
            }
        } catch (\Throwable $e) {
            Log::warning('[Stripe] Webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'signature verification failed'], 400);
        }

        $type = $event->type ?? $event['type'] ?? 'unknown';
        Log::info('[Stripe] Event received', ['type' => $type]);

        switch ($type) {
            case 'checkout.session.completed': {
                $session = $event->data->object;

                // Try to identify the user by client_reference_id first, then by email
                $user = null;
                if (!empty($session->client_reference_id)) {
                    $user = User::find((int)$session->client_reference_id);
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

                // Mark premium on successful checkout
                $user->is_premium = 1;
                $user->save();

                Log::info('[Stripe] User upgraded to premium', ['user_id' => $user->id]);
                break;
            }

            case 'customer.subscription.updated': {
                $sub = $event->data->object;
                // Find user by email or by metadata if you store it
                $email = $sub->customer_email ?? null; // often not present
                $user  = $email ? User::where('email', $email)->first() : null;

                if ($user) {
                    $isActive = ($sub->status === 'active' || $sub->status === 'trialing');
                    $user->is_premium = $isActive ? 1 : 0;
                    $user->save();
                    Log::info('[Stripe] Subscription updated -> premium='.$user->is_premium, ['user_id' => $user->id]);
                }
                break;
            }

            case 'customer.subscription.deleted': {
                // Subscription cancelled/expired: attempt to find by email if present
                $sub   = $event->data->object;
                $email = $sub->customer_email ?? null;
                $user  = $email ? User::where('email', $email)->first() : null;

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
}
