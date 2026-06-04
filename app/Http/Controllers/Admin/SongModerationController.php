<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;
use Illuminate\Support\Facades\Schema;


class SongModerationController extends Controller
{
    /**
     * Ensure only admins can moderate songs.
     */
    protected function ensureAdmin(Request $request): void
    {
        $user = $request->user();

        $isAdmin =
            ($user->is_admin ?? false) ||
            (($user->role ?? null) === 'admin');

        if (!$user || !$isAdmin) {
            abort(403, 'Admin access required.');
        }
    }

    public function approve(Request $request, Song $song)
    {
        $this->ensureAdmin($request);

        $song->status = 'published';

        if (Schema::hasColumn('songs', 'is_published')) {
            $song->is_published = true;
        }

        $song->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Song published',
            'data'    => $song,
        ]);
    }

    public function reject(Request $request, Song $song)
    {
        $this->ensureAdmin($request);

        $reason = $request->input('reason');

        $song->status = 'rejected';

        if (Schema::hasColumn('songs', 'is_published')) {
            $song->is_published = false;
        }

        if (Schema::hasColumn('songs', 'reject_reason')) {
            $song->reject_reason = $reason;
        }

        $song->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Song rejected',
        ]);
    }
}