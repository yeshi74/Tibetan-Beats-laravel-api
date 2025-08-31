<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function updateProfileImage(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // store new image
        $path = $request->file('image')->store('profile_images', 'public');

        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile image updated successfully',
            'profile_image_url' => $user->profile_image_url,
        ]);
    }

}
