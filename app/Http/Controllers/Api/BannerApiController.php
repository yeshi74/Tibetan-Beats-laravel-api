<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BannerApiController extends Controller
{
    public function active(Request $request)
    {
        $now = Carbon::now();

        $banners = Banner::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('position') // or created_at desc
            ->get()
            ->map(function (Banner $b) {
                return [
                    'id'          => $b->id,
                    'title'       => $b->title,
                    'subtitle'    => $b->subtitle,
                    'image_url'   => $b->image_path
                        ? asset('storage/'.$b->image_path)
                        : null,
                    'cta_label'   => $b->cta_label,
                    'cta_type'    => $b->cta_type,     // 'song' | 'playlist' | 'url'
                    'cta_target'  => $b->cta_target,   // id or url
                ];
            });

        return response()->json([
            'status'  => 'success',
            'banners' => $banners,
        ]);
    }
}
