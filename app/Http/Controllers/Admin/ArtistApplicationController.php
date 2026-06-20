<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistApplication;
use App\Models\Role;
use Illuminate\Http\Request;

class ArtistApplicationController extends Controller
{
    public function index()
    {
        $applications = ArtistApplication::with('user')
            ->latest()
            ->paginate(20);

        return view(
            'admin.artist-applications.index',
            compact('applications')
        );
    }

    public function show(ArtistApplication $application)
    {
        return view(
            'admin.artist-applications.show',
            compact('application')
        );
    }

    public function approve(ArtistApplication $application)
    {
        if ($application->status !== 'pending') {
            return back()->with(
                'error',
                'Application already processed.'
            );
        }

        $user = $application->user;

        $artist = Artist::create([
            'user_id' => $user->id,
            'display_name' => $application->display_name,
            'real_name' => $application->real_name,
            'country' => $application->country,
            'profile_image' => $application->profile_image,
            'social_links' => $application->social_links,
            'meta' => $application->meta,
            'verified' => true,
        ]);

        $artistRoleId = Role::where(
            'name',
            'artist'
        )->value('id');

        if ($artistRoleId) {
            $user->role_id = $artistRoleId;
            $user->save();
        }

        $application->update([
            'status' => 'approved',
        ]);

        return back()->with(
            'success',
            'Artist application approved.'
        );
    }

    public function reject(
        Request $request,
        ArtistApplication $application
    ) {
        $application->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with(
            'success',
            'Artist application rejected.'
        );
    }
}