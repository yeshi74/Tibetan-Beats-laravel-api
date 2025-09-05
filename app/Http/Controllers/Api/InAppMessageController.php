<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InAppMessage;
use App\Models\InAppMessageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InAppMessageController extends Controller
{
    /**
     * GET /api/in-app-messages
     * Query params:
     *  - locale (en)
     *  - country
     *  - device_id (if not authenticated)
     */
    public function index(Request $req)
    {
        try {
            $locale = $req->get('locale','en');
            $country = $req->get('country');
            $deviceId = $req->get('device_id');
            $user = $req->user();

            $query = InAppMessage::active()->orderBy('priority','desc');

            // Fetch up to 20 messages
            $messages = $query->take(20)->get();

            $out = [];
            foreach ($messages as $m) {
                if (!$this->matchesAudience($m->audience, $user, $country)) continue;

                // check if user/device dismissed with persist
                $view = $this->getViewRecord($m->id, $user, $deviceId);
                if ($m->dismiss_persists && $view && $view->dismissed_at) {
                    continue;
                }

                // check per-user impression limit
                if ($view && $m->max_impressions_per_user && $view->impressions >= $m->max_impressions_per_user) {
                    continue;
                }

                // check global cap
                if ($m->max_impressions_global && $m->impressions_count >= $m->max_impressions_global) {
                    continue;
                }

                $out[] = [
                    'id' => $m->id,
                    'key' => $m->key,
                    'type' => $m->type,
                    'title' => $m->title[$locale] ?? ($m->title['en'] ?? ''),
                    'body'  => $m->body[$locale] ?? ($m->body['en'] ?? ''),
                    'image_url' => $m->image_url ? url($m->image_url) : null,
                    'cta' => $m->cta ? (array)$m->cta : null,
                    'priority' => $m->priority,
                    'dismissible' => (bool)$m->dismissible,
                    'dismiss_persists' => (bool)$m->dismiss_persists,
                    'max_impressions_per_user' => $m->max_impressions_per_user,
                ];
            }

            return response()->json(['status'=>'success','data'=>$out]);
        } catch (\Throwable $e) {
            \Log::error('[InAppMessages] index failed', ['error'=>$e->getMessage()]);
            return response()->json(['status'=>'error','message'=>'Failed'],'500');
        }
    }

    protected function matchesAudience($audience, $user, $country)
    {
        if (!$audience || $audience === 'all') return true;
        if (str_starts_with($audience, 'role:')) {
            $role = substr($audience,strlen('role:'));
            return $user && optional($user->role)->name === $role;
        }
        if (str_starts_with($audience, 'users:')) {
            $s = substr($audience, strlen('users:'));
            $ids = json_decode($s, true);
            if (!is_array($ids)) {
                $ids = array_filter(array_map('intval', explode(',', trim($s, '[] '))));
            }
            return $user && in_array($user->id, $ids);
        }
        if (str_starts_with($audience, 'country:')) {
            $c = substr($audience, strlen('country:'));
            return $country && strtoupper($country) === strtoupper($c);
        }
        if ($audience === 'premium') {
            return $user && ((bool)$user->is_premium || optional($user->role)->name === 'premium');
        }
        // default fallback: treat as all
        return true;
    }

    protected function getViewRecord($messageId, $user, $deviceId)
    {
        if ($user) {
            return InAppMessageView::where('in_app_message_id', $messageId)->where('user_id', $user->id)->first();
        }
        if ($deviceId) {
            return InAppMessageView::where('in_app_message_id', $messageId)->where('device_id', $deviceId)->first();
        }
        return null;
    }

    /**
     * POST /api/in-app-messages/{id}/impression
     * body: { device_id?: string }
     */
    public function impression(Request $req, InAppMessage $inAppMessage)
    {
        $user = $req->user();
        $deviceId = $req->input('device_id');
        DB::transaction(function() use ($inAppMessage, $user, $deviceId) {
            $q = InAppMessageView::where('in_app_message_id', $inAppMessage->id);
            if ($user) $q->where('user_id', $user->id); else $q->where('device_id', $deviceId);
            $view = $q->first();

            if (!$view) {
                $view = InAppMessageView::create([
                    'in_app_message_id' => $inAppMessage->id,
                    'user_id' => $user?->id,
                    'device_id' => $user ? null : $deviceId,
                    'impressions' => 0,
                ]);
            }

            $view->increment('impressions');
            $view->last_shown_at = now();
            $view->save();

            $inAppMessage->increment('impressions_count');
        });

        return response()->json(['status'=>'success']);
    }

    /**
     * POST /api/in-app-messages/{id}/click
     */
    public function click(Request $req, InAppMessage $inAppMessage)
    {
        $inAppMessage->increment('clicks_count');
        return response()->json(['status'=>'success']);
    }

    /**
     * POST /api/in-app-messages/{id}/dismiss
     * body: { device_id?: string }
     */
    public function dismiss(Request $req, InAppMessage $inAppMessage)
    {
        $user = $req->user();
        $deviceId = $req->input('device_id');

        $q = InAppMessageView::where('in_app_message_id', $inAppMessage->id);
        if ($user) $q->where('user_id', $user->id); else $q->where('device_id', $deviceId);
        $view = $q->first();

        if (!$view) {
            $view = InAppMessageView::create([
                'in_app_message_id' => $inAppMessage->id,
                'user_id' => $user?->id,
                'device_id' => $user ? null : $deviceId,
                'impressions' => 0,
            ]);
        }

        $view->dismissed_at = now();
        $view->save();

        return response()->json(['status'=>'success']);
    }
}
