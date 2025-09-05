<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationAdminController extends Controller
{
    public function index(Request $req)
    {
        $q = AdminNotification::query()->with('creator')->latest();

        if ($s = $req->get('q')) {
            $q->where(fn($w) => $w->where('title','like', "%{$s}%")
                ->orWhere('body','like', "%{$s}%"));
        }

        return view('admin.notifications.index', [
            'notifications' => $q->paginate(30)->withQueryString(),
            'query' => $req->get('q',''),
        ]);
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $req)
    {
        $req->validate([
            'title' => 'required|string|max:200',
            'body'  => 'nullable|string|max:2000',
            'audience' => 'required|string', // validate more in UI
            'payload' => 'nullable|json',
            'scheduled_at' => 'nullable|date',
            'type' => 'nullable|string',
        ]);

        $n = AdminNotification::create([
            'title' => $req->title,
            'body'  => $req->body,
            'type'  => $req->type,
            'payload' => $req->payload ? json_decode($req->payload, true) : null,
            'audience' => $req->audience,
            'scheduled_at' => $req->scheduled_at,
            'created_by' => auth()->id(),
        ]);

        // If scheduled_at not set or in the past, dispatch immediately
        if (!$n->scheduled_at || $n->scheduled_at->isPast()) {
            SendAdminNotificationJob::dispatch($n);
            $this->audit('notification.sent', ['id' => $n->id, 'audience' => $n->audience]);
        } else {
            // schedule the job for scheduled_at
            SendAdminNotificationJob::dispatch($n)->delay($n->scheduled_at);
            $this->audit('notification.scheduled', ['id'=>$n->id, 'when'=>$n->scheduled_at->toDateTimeString()]);
        }

        return redirect()->route('admin.notifications.index')->with('status','Notification queued');
    }

    public function show(AdminNotification $notification)
    {
        return view('admin.notifications.show', ['n'=>$notification->load('creator')]);
    }

    public function destroy(AdminNotification $notification)
    {
        $notification->delete();
        $this->audit('notification.deleted', ['id'=>$notification->id]);
        return back()->with('status','Deleted');
    }

    private function audit(string $event, array $payload = [])
    {
        DB::table('admin_audits')->insert([
            'admin_id' => auth()->id(),
            'event' => $event,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
