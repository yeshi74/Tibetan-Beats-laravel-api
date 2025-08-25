<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhooks.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret') ?? env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Throwable $e) {
            \Log::error('[Stripe] Signature verification failed: '.$e->getMessage());
            return response('Invalid signature', 400);
        }

        \Log::info('[Stripe] Event received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed': {
                $s = $event->data->object; // \Stripe\Checkout\Session

                // Best: use client_reference_id set when creating the session
                $user = null;
                if (!empty($s->client_reference_id)) {
                    $user = User::find($s->client_reference_id);
                } elseif (!empty($s->metadata->user_id)) {
                    $user = User::find($s->metadata->user_id);
                } elseif (!empty($s->customer_details->email)) {
                    $user = User::where('email', $s->customer_details->email)->first();
                }

                if (!$user) {
                    \Log::warning('[Stripe] Could not map checkout session to user', [
                        'client_reference_id' => $s->client_reference_id ?? null,
                        'customer_email'      => $s->customer_email ?? ($s->customer_details->email ?? null),
                        'metadata'            => $s->metadata ?? null,
                    ]);
                    break;
                }

                $user->is_premium           = 1;
                $user->stripe_customer_id   = $s->customer ?? $user->stripe_customer_id;
                $user->stripe_subscription_id = $s->subscription ?? $user->stripe_subscription_id;
                $user->save();

                \Log::info('[Stripe] Premium granted', ['user_id' => $user->id]);
                break;
            }

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted': {
                $sub = $event->data->object; // \Stripe\Subscription
                $customerId = $sub->customer;

                if (!$customerId) break;

                $user = User::where('stripe_customer_id', $customerId)->first();
                if (!$user) {
                    \Log::warning('[Stripe] No user for customer', ['customer' => $customerId]);
                    break;
                }

                $active = ($event->type === 'customer.subscription.updated') &&
                          in_array($sub->status, ['active','trialing']);
                $user->is_premium = $active ? 1 : 0;
                $user->stripe_subscription_id = $sub->id;
                $user->save();

                \Log::info('[Stripe] Subscription status sync', [
                    'user_id' => $user->id,
                    'status'  => $sub->status,
                ]);
                break;
            }

            default:
                \Log::info('[Stripe] Unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Try to find a user based on data we pass into the Checkout Session.
     */
    protected function findUserFromSession($session): ?User
    {
        // Prefer client_reference_id if you set user_id there.
        if (!empty($session->client_reference_id)) {
            return User::find($session->client_reference_id);
        }

        // Otherwise fall back to email (works if you send customer_email)
        $email = $session->customer_details->email ?? $session->customer_email ?? null;
        if ($email) return User::where('email', $email)->first();

        // Last resort: look up by Stripe customer id stored on user (if you store it)
        if (!empty($session->customer)) {
            return User::where('stripe_customer_id', $session->customer)->first();
        }

        return null;
    }

    protected function findUserByStripeCustomer($stripeCustomerId): ?User
    {
        if (!$stripeCustomerId) return null;
        return User::where('stripe_customer_id', $stripeCustomerId)->first();
    }
}
