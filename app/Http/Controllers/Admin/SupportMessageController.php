<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;

class SupportMessageController extends Controller
{
    public function index()
    {
        $messages = SupportMessage::with('user')
            ->latest()
            ->paginate(20);

        return view(
            'admin.support.index',
            compact('messages')
        );
    }

    public function show(SupportMessage $supportMessage)
    {
        return view(
            'admin.support.show',
            compact('supportMessage')
        );
    }

    public function resolve(SupportMessage $supportMessage)
    {
        $supportMessage->update([
            'status' => 'resolved',
        ]);

        return back()->with(
            'success',
            'Message marked as resolved.'
        );
    }
}
