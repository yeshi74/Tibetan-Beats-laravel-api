<?php

namespace App\Http\Controllers;

use App\Models\Song;

class ShareController extends Controller
{
    public function song(Song $song)
    {
        return view('share.song', compact('song'));
    }
}