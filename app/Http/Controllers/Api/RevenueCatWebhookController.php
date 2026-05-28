<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RevenueCatWebhookController extends Controller
{
    private const ACTIVE_EVENTS = [
        'INITIAL_PURCHASE',
        'RENEWAL',
        'UNCANCELLATION',
        'PRODUCT_CHANGE',
        'NON_RENEWING_PURCHASE',
    ];

    private const INACTIVE_EVENTS = [
        'EXPIRATION',
    ];

    /**
     * POST /api/revenuecat/webhook
     */
    public function handle(Request $request)
    {
        $configuredToken = config('services.revenuecat.webhook_token');
        $authorization = $request->header('Authorization');

        if ($configuredToken && !hash_equals('Bearer ' . $configuredToken, (string) $authorization)) {
            Log::warning('[PremiumTrace][RevenueCat] webhook rejected', [
                'reason' => 'invalid_authorization',
                'has_authorization' => filled($authorization),
            ]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = $request->input('event', []);
        $type = strtoupper((string) data_get($event, 'type', 'UNKNOWN'));
        $appUserId = data_get($event, 'app_user_id') ?: data_get($event, 'original_app_user_id');
        $expirationAtMs = data_get($event, 'expiration_at_ms');
        $premiumEndsAt = $expirationAtMs
            ? Carbon::createFromTimestampMs((int) $expirationAtMs)
            : null;

        Log::info('[PremiumTrace][RevenueCat] event received', [
            'type' => $type,
            'app_user_id' => $appUserId,
            'product_id' => data_get($event, 'product_id'),
            'entitlement_ids' => array_keys((array) data_get($event, 'entitlements', [])),
            'expiration_at_ms' => $expirationAtMs,
            'premium_ends_at' => optional($premiumEndsAt)->toIso8601String(),
        ]);

        $user = $this->findUser($appUserId);

        if (!$user) {
            Log::warning('[PremiumTrace][RevenueCat] user not found', [
                'type' => $type,
                'app_user_id' => $appUserId,
                'aliases' => data_get($event, 'aliases'),
            ]);

            return response()->json(['received' => true, 'user_found' => false]);
        }

        $before = [
            'is_premium' => (bool) $user->is_premium,
            'premium_ends_at' => optional($user->premium_ends_at)->toIso8601String(),
        ];

        if (in_array($type, self::ACTIVE_EVENTS, true)) {
            $user->is_premium = true;
            $user->premium_ends_at = $premiumEndsAt ?? $user->premium_ends_at;
            $user->save();
        } elseif (in_array($type, self::INACTIVE_EVENTS, true)) {
            $user->is_premium = false;
            $user->premium_ends_at = $premiumEndsAt;
            $user->save();
        }

        $user->refresh();

        Log::info('[PremiumTrace][RevenueCat] user premium sync', [
            'user_id' => $user->id,
            'type' => $type,
            'before' => $before,
            'after' => [
                'is_premium' => (bool) $user->is_premium,
                'premium_ends_at' => optional($user->premium_ends_at)->toIso8601String(),
            ],
            'handled' => in_array($type, array_merge(self::ACTIVE_EVENTS, self::INACTIVE_EVENTS), true),
        ]);

        return response()->json(['received' => true, 'user_found' => true]);
    }

    private function findUser(?string $appUserId): ?User
    {
        if (!$appUserId) {
            return null;
        }

        if (ctype_digit($appUserId)) {
            return User::find((int) $appUserId);
        }

        if (filter_var($appUserId, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $appUserId)->first();
        }

        return null;
    }
}
