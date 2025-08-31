<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Generate reset token (for app usage)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Create token manually
        $token = Password::createToken($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Reset token generated',
            'data'    => [
                'token' => $token,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Handle reset password with token
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Password has been reset successfully',
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => __($status),
        ], 500);
    }
}
