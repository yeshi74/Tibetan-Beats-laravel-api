<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

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
}
