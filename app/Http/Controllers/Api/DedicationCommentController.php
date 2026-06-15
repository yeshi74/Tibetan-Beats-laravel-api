<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dedication;
use App\Models\DedicationComment;
use Illuminate\Support\Facades\Auth;

class DedicationCommentController extends Controller
{
    public function index(Dedication $dedication)
    {
        // Only allow comments on approved public dedications
        if ($dedication->status !== 'approved') {
            return response()->json([
                'message' => 'Comments not available.'
            ], 404);
        }

        $comments = $dedication->comments()
            ->with('user:id,name,profile_image')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,

                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'profile_image_url' => $comment->user->profile_image_url,
                    ],

                    'created_at' => optional(
                        $comment->created_at
                    )->toIso8601String(),
                ];
            });

        return response()->json($comments);
    }

    public function store(Request $request, Dedication $dedication)
    {
        // Only approved public dedications can receive comments
        if ($dedication->status !== 'approved') {
            return response()->json([
                'message' => 'Comments not available.'
            ], 404);
        }

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:500'],
        ]);

        $comment = DedicationComment::create([
            'dedication_id' => $dedication->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        $comment->load('user:id,name,profile_image');

        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment,

            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'profile_image_url' => $comment->user->profile_image_url,
            ],

            'created_at' => optional(
                $comment->created_at
            )->toIso8601String(),
        ], 201);
    }

    public function destroy(DedicationComment $comment)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Only comment owner can delete
        if ($comment->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted.'
        ]);
    }

}
