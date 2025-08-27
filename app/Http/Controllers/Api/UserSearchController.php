<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserSearchController extends Controller {
    public function search(Request $request)
	{
	    $q = (string) $request->query('q', '');
	    $users = \App\Models\User::query()
	        ->where('name', 'like', "%{$q}%")
	        ->select('id','name')   // 👈 use name
	        ->orderBy('name')
	        ->limit(20)
	        ->get();

	    return response()->json(['status' => 'success', 'data' => $users]);
	}

}