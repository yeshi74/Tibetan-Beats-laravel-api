<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $req) {
        $req->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users',
            'mobile' => 'nullable|string|unique:users',
            'password'=>'required|string|confirmed'
        ]);
        $user = User::create([
            'name'=>$req->name,
            'email'=>$req->email,
            'mobile' => $request->mobile,
            'password'=>Hash::make($req->password),
            'is_admin'=>0,
        ]);
        $token = $user->createToken('MobileToken')->plainTextToken;
        return response()->json(['message'=>'Registered','access_token'=>$token]);
    }

    public function login(Request $req) {
        $req->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);
        $user = User::where('email',$req->email)->first();
        if(!$user || !Hash::check($req->password, $user->password)) {
            return response()->json(['message'=>'Invalid credentials'],401);
        }
        $token = $user->createToken('MobileToken')->plainTextToken;
        return response()->json(['access_token'=>$token]);
    }

    public function logout(Request $req) {
        $req->user()->tokens()->delete();
        return response()->json(['message'=>'Logged out']);
    }

    public function user(Request $req) {
        return response()->json($req->user());
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string'
        ]);

        $otp = rand(100000, 999999); // generate 6-digit OTP
        $expiresAt = now()->addMinutes(5); // OTP valid for 5 minutes

        $user = User::firstOrCreate(
            ['mobile' => $request->mobile],
            ['name' => 'New User', 'email' => null, 'password' => Hash::make(Str::random(10)), 'is_admin' => 0]
        );

        $user->otp_code = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();

        // 🚀 You can integrate Twilio/SMS API here
        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $otp  // Show OTP for testing (remove in production)
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'otp' => 'required|string'
        ]);

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json(['message' => 'Mobile number not found'], 404);
        }

        if (
            $user->otp_code !== $request->otp ||
            now()->greaterThan($user->otp_expires_at)
        ) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        // OTP is valid, login user
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('OTPLoginToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => $user
        ]);
    }

    public function loginWithMobile(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid mobile or password'], 401);
        }

        $token = $user->createToken('MobileToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => $user,
        ]);
    }

}

