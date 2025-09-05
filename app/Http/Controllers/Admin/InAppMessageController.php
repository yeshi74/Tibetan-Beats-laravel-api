<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InAppMessageController extends Controller
{
    public function index(Request $req)
    {
        $q = InAppMessage::query();

        if ($s = $req->get('q')) {
            $q->where(function($w) use ($s) {
                $w->where('key','like',"%{$s}%")
                  ->orWhere('title','like',"%{$s}%")
                  ->orWhere('body','like',"%{$s}%");
            });
        }

        return view('admin.in_app_messages.index', [
            'messages' => $q->orderBy('priority','desc')->paginate(30)->withQueryString(),
            'query' => $req->get('q',''),
        ]);
    }

    public function create()
    {
        return view('admin.in_app_messages.create');
    }

    public function store(Request $req)
    {
        $req->validate([
            'key' => 'nullable|string|max:191|unique:in_app_messages,key',
            'type' => 'required|in:modal,banner,card,bottom_sheet,interstitial',
            'title_en' => 'required|string|max:250',
            'body_en' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:2048',
            'cta_type' => 'nullable|in:route,url',
            'cta_value' => 'nullable|string|max:500',
            'audience' => 'required|string',
            'priority' => 'required|integer|min:0|max:999',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $data = $req->only([
            'type','audience','priority','start_at','end_at'
        ]);

        // localized simple approach: store under 'en' key
        $data['title'] = ['en' => $req->input('title_en')];
        $data['body']  = ['en' => $req->input('body_en')];

        // cta
        if ($req->filled('cta_type') && $req->filled('cta_value')) {
            $data['cta'] = ['type' => $req->input('cta_type'), 'value' => $req->input('cta_value'), 'label' => ['en' => $req->input('cta_label_en')]];
        }

        // image (optional upload)
        if ($req->hasFile('image')) {
            $path = $req->file('image')->store('in-app-messages','public');
            $data['image_url'] = '/storage/'.$path;
        } elseif ($req->filled('image_url')) {
            $data['image_url'] = $req->input('image_url');
        }

        $data['dismissible'] = $req->boolean('dismissible', true);
        $data['dismiss_persists'] = $req->boolean('dismiss_persists', true);
        $data['status'] = $req->input('status','active');
        $data['created_by'] = auth()->id();
        $data['key'] = $req->input('key') ?: Str::slug($req->input('title_en').' '.uniqid(), '_');

        $m = InAppMessage::create($data);

        return redirect()->route('admin.in_app_messages.index')->with('status','Message created');
    }

    public function show(InAppMessage $inAppMessage)
    {
        return view('admin.in_app_messages.show', ['m' => $inAppMessage->load('creator')]);
    }

    public function edit(InAppMessage $inAppMessage)
    {
        return view('admin.in_app_messages.edit', ['m' => $inAppMessage]);
    }

    public function update(Request $req, InAppMessage $inAppMessage)
    {
        $req->validate([
            'type' => 'required|in:modal,banner,card,bottom_sheet,interstitial',
            'title_en' => 'required|string|max:250',
            'body_en' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:2048',
            'cta_type' => 'nullable|in:route,url',
            'cta_value' => 'nullable|string|max:500',
            'audience' => 'required|string',
            'priority' => 'required|integer|min:0|max:999',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $data = $req->only(['type','audience','priority','start_at','end_at']);
        $data['title'] = ['en' => $req->input('title_en')];
        $data['body']  = ['en' => $req->input('body_en')];

        if ($req->filled('cta_type') && $req->filled('cta_value')) {
            $data['cta'] = ['type' => $req->input('cta_type'), 'value' => $req->input('cta_value'), 'label' => ['en' => $req->input('cta_label_en')]];
        } else {
            $data['cta'] = null;
        }

        if ($req->hasFile('image')) {
            $path = $req->file('image')->store('in-app-messages','public');
            $data['image_url'] = '/storage/'.$path;
        } elseif ($req->filled('image_url')) {
            $data['image_url'] = $req->input('image_url');
        }

        $data['dismissible'] = $req->boolean('dismissible', true);
        $data['dismiss_persists'] = $req->boolean('dismiss_persists', true);
        $data['status'] = $req->input('status','active');

        $inAppMessage->update($data);

        return back()->with('status','Message updated');
    }

    public function destroy(InAppMessage $inAppMessage)
    {
        $inAppMessage->delete();
        return back()->with('status','Deleted');
    }
}
