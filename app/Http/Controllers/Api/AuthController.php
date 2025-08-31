<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $req) {
        $req->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name'     => $req->name,
            'email'    => $req->email,
            'password' => Hash::make($req->password),
            'is_admin' => 0,
        ]);

        // 🔹 Send verification email
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registered successfully. Please verify your email before logging in.',
        ], 201);
    }

    public function login(Request $req) {
        $req->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email first.'], 403);
        }

        $token = $user->createToken('MobileToken')->plainTextToken;

        return response()->json([
            'message'      => 'Login successful',
            'access_token' => $token,
            'name'         => $user->name,
            'email'        => $user->email,
        ]);
    }

    public function logout(Request $req) {
        $req->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $req) {
        return response()->json($req->user());
    }

    public function updateProfile(Request $req)
    {
        $req->validate([
            'name' => 'required|string|min:2|max:255',
            // If you want to allow email change later, add:
            // 'email' => 'nullable|email|unique:users,email,' . $req->user()->id,
        ]);

        $user = $req->user();
        $user->name = $req->name;
        $user->save();

        return response()->json([
            'message' => 'Profile updated',
            'name'    => $user->name,
            'email'   => $user->email,
        ]);
    }

    public function changePassword(Request $req)
    {
        $req->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
            // client must send new_password_confirmation
        ]);

        $user = $req->user();

        if (!Hash::check($req->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($req->new_password);
        $user->save();

        // (Optional) revoke other tokens to force re-login elsewhere:
        // $user->tokens()->where('id', '!=', $req->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password changed successfully']);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Password::sendResetLink(
            $request->only('email')
        );

        if ($status === \Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 400);
    }

}
