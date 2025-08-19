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
}
