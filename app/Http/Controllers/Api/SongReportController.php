<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SongReport;
use Illuminate\Http\Request;

class SongReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_id' => [
                'required',
                'exists:songs,id',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'details' => [
                'nullable',
                'string',
            ],
        ]);

        $existing = SongReport::where(
                'user_id',
                auth()->id()
            )
            ->where(
                'song_id',
                $validated['song_id']
            )
            ->where(
                'status',
                'pending'
            )
            ->exists();

        if ($existing) {
            return response()->json([
                'message' =>
                    'You already reported this song.'
            ], 422);
        }

        $report = SongReport::create([
            'user_id' => auth()->id(),
            'song_id' => $validated['song_id'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Report submitted successfully',
            'report' => $report,
        ]);
    }
}