<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IapController extends Controller
{
    public function verify(Request $req)
    {
      $platform = $req->input('platform'); // 'android' | 'ios'
      $productId = $req->input('product_id');
      $verificationData = $req->input('verification_data.serverVerificationData');
      $source = $req->input('verification_data.source'); // 'google_play' or 'app_store'

      // TODO: verify with platform:
      // ANDROID: Use Google Play Developer API (purchases.subscriptions.get)
      //   - Requires service account + packageName + productId + purchaseToken
      //   - purchaseToken = verificationData for Google
      //
      // iOS: Use App Store Server API / AppStore receipts (verifyTransaction)
      //   - Send transaction or appStoreReceipt to Apple endpoint

      $isValid = false; // ← do real verification
      $isPremium = false;

      // Example (pseudo):
      // if ($platform === 'android') {
      //   $token = $verificationData;
      //   $isValid = GoogleVerifier::verifySubscription($packageName, $productId, $token);
      // }
      // if ($platform === 'ios') {
      //   $isValid = AppleVerifier::verifyTransaction($verificationData);
      // }
      //
      // If valid and active (not expired/canceled), set $isPremium = true and
      // persist on your User model.

      return response()->json([
        'valid' => $isValid,
        'is_premium' => $isPremium,
      ]);
    }
}
