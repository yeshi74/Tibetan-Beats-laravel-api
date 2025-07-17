<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $req) {
        $req->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users',
            'password'=>'required|string|confirmed'
        ]);
        $user = User::create([
            'name'=>$req->name,
            'email'=>$req->email,
            'password'=>Hash::make($req->password),
            'is_admin' => 0,
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
}

