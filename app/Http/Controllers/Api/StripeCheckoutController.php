<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{
    /**
     * Generic checkout creation using explicit price_id from request
     */
public function create(Request $request)
{
    $request->validate([
        'plan' => 'required|string|in:monthly,yearly',
    ]);

    $user = $request->user();
    $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

    // Map frontend plan to Stripe price IDs
    $priceId = $request->plan === 'yearly'
        ? env('STRIPE_PRICE_YEARLY')   // set in .env
        : env('STRIPE_PRICE_MONTHLY'); // set in .env

    $session = $stripe->checkout->sessions->create([
        'mode' => 'subscription',
        'line_items' => [[
            'price'    => $priceId,
            'quantity' => 1,
        ]],
        'success_url' => config('app.url') . '/stripe/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => config('app.url') . '/stripe/cancel',
        'client_reference_id' => (string) $user->id,
        'customer_email'      => $user->email,
        'metadata'            => [
            'user_id' => (string) $user->id,
            'email'   => $user->email,
            'plan'    => $request->plan,
        ],
    ]);

    return response()->json([
        'id'  => $session->id,
        'url' => $session->url,
    ]);
}


    /**
     * Checkout creation that accepts "plan" (monthly/yearly) from Flutter
     */
    public function createCheckout(Request $request)
    {
        $request->validate([
            'plan' => 'nullable|string|in:monthly,yearly',
        ]);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = $request->user();
        $plan = $request->input('plan', 'monthly'); // default monthly

        // 👇 Resolve Stripe price IDs from .env
        $priceId = $plan === 'yearly'
            ? env('STRIPE_PRICE_YEARLY')
            : env('STRIPE_PRICE_MONTHLY');

        if (!$priceId) {
            return response()->json(['error' => 'Invalid Stripe price ID'], 422);
        }

        $successUrl = config('app.url').'/premium/success?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = config('app.url').'/premium/cancel';

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
            'client_reference_id' => (string) $user->id,
            'customer_email' => $user->email,
            'metadata' => [
                'plan' => $plan,
                'user_id' => (string) $user->id,
            ],
        ]);

        return response()->json([
            'id'  => $session->id,
            'url' => $session->url,
        ]);
    }

    /**
     * Billing portal (manage subscription)
     */
    public function createBillingPortal(Request $request)
    {
        $stripe = new StripeClient(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        $customerEmail = $request->user()->email;
        $customers = $stripe->customers->all(['email' => $customerEmail, 'limit' => 1]);
        $customer = $customers->data[0] ?? null;

        if (!$customer) {
            return response()->json(['error' => 'No customer found'], 404);
        }

        $returnUrl = URL::to('/');
        $portal = $stripe->billingPortal->sessions->create([
            'customer' => $customer->id,
            'return_url' => $returnUrl,
        ]);

        return response()->json(['url' => $portal->url]);
    }
}
