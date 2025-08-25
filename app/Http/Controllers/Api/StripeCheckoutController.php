<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Stripe\StripeClient;

class StripeCheckoutController extends Controller
{

    public function create(Request $request)
    {
        $request->validate([
            'price_id' => 'required|string', // your recurring price id from Stripe
        ]);

        $user = $request->user();
        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'line_items' => [[
                'price'    => $request->price_id,
                'quantity' => 1,
            ]],
            'success_url' => config('app.url') . '/stripe/success', // placeholder
            'cancel_url'  => config('app.url') . '/stripe/cancel',  // placeholder

            // 👇 attach identity
            'client_reference_id' => (string) $user->id,
            'customer_email'      => $user->email,
            'metadata'            => [
                'user_id' => (string) $user->id,
                'email'   => $user->email,
            ],
            // optional: collect billing
            'customer_creation'   => 'if_required',
        ]);

        return response()->json([
            'id'  => $session->id,
            'url' => $session->url,
        ]);
    }
    
    public function createCheckout(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $successUrl = config('app.url').'/stripe/success?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = config('app.url').'/stripe/cancel';

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'line_items' => [[
                'price' => env('STRIPE_PRICE_ID'),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
            'client_reference_id' => (string) auth()->id(),
            'customer_email' => auth()->user()->email,
        ]);

        return response()->json(['id' => $session->id, 'url' => $session->url]);
    }
        
    public function createBillingPortal(Request $request)
    {
        $stripe = new StripeClient(config('services.stripe.secret') ?? env('STRIPE_SECRET'));

        // you might want to track/save customer id; this is a simple fallback
        $customerEmail = $request->user()->email;
        $customers = $stripe->customers->all(['email' => $customerEmail, 'limit' => 1]);
        $customer = $customers->data[0] ?? null;

        if (!$customer) {
            return response()->json(['error' => 'No customer found'], 404);
        }

        $returnUrl = URL::to('/'); // where to send the user after managing billing
        $portal = $stripe->billingPortal->sessions->create([
            'customer' => $customer->id,
            'return_url' => $returnUrl,
        ]);

        return response()->json(['url' => $portal->url]);
    }
}
