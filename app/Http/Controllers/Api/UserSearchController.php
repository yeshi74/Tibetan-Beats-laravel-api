<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserSearchController extends Controller {
    public function search(Request $request)
	{
	    $q = (string) $request->query('q', '');
	    $users = User::query()
		    ->where('name', 'like', "%{$q}%")
		    ->select('id', 'name', 'profile_image')
		    ->orderBy('name')
		    ->limit(20)
		    ->get()
		    ->map(function ($user) {
		        return [
		            'id' => $user->id,
		            'name' => $user->name,
		            'profile_image_url' => $user->profile_image
		                ? asset('storage/' . $user->profile_image)
		                : null,
		        ];
		    });

	    return response()->json(['status' => 'success', 'data' => $users]);
	}

}