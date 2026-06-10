<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Artists cannot self-delete
        if ($user->artist) {
            return response()->json([
                'message' =>
                    'Artist accounts must contact support for deletion.'
            ], 422);
        }

        // revoke all tokens
        $user->tokens()->delete();

        // soft delete account
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }


}
