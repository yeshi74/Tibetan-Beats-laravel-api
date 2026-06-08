<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dedication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class DedicationModerationController extends Controller
{
    public function index(\Illuminate\Http\Request $req)
    {
        $status = $req->string('status')->toString();

        $q = \App\Models\Dedication::query()
            ->with(['reviewer','user','song'])
            ->latest();

        if ($status !== '') {
            $q->where('status', $status);
        }

        return view('admin.moderation.dedications.index', [
            'dedications' => $q->paginate(30)->withQueryString(),
            'status'      => $status, // optional if you want to use it in Blade
        ]);
    }

    public function show(Dedication $dedication)
    {
        return view('admin.moderation.dedications.show', [
            'd' => $dedication->load(['reviewer','user','song'])
        ]);
    }

    public function approve(Request $req, Dedication $dedication)
    {
        if ($dedication->status !== 'approved') {

            $dedication->fill([
                'status'        => 'approved',
                'reviewed_by'   => auth()->id(),
                'reviewed_at'   => now(),
                'reject_reason' => null,
            ])->save();

            $dedication->loadMissing(['user', 'song']);

            // Notify recipient if this dedication was sent to an app user
            if ($dedication->to_user_id) {

                Notification::create([
                    'user_id' => $dedication->to_user_id,

                    'title' => 'New Song Dedication',

                    'body' =>
                        ($dedication->user->name ?? 'Someone')
                        . ' dedicated "'
                        . ($dedication->song->title ?? 'a song')
                        . '" to you.',

                    'type' => 'dedication',

                    'data' => [
                        'dedication_id' => $dedication->id,
                        'song_id'       => $dedication->song_id,

                        'sender_name'   => $dedication->user->name ?? 'Someone',
                        'song_title'    => $dedication->song->title ?? 'a song',
                        'message'       => $dedication->message,
                    ],

                    'is_read' => false,
                ]);
            }

            // Notify sender that dedication was delivered
            Notification::create([
                'user_id' => $dedication->user_id,

                'title' => 'Dedication Delivered',

                'body' =>
                    'Your dedication of "'
                    . ($dedication->song->title ?? 'a song')
                    . '" to '
                    . ($dedication->to_name ?? 'someone')
                    . ' has been delivered.',

                'type' => 'dedication_delivered',

                'data' => [
                    'dedication_id'  => $dedication->id,
                    'song_id'        => $dedication->song_id,
                    'song_title'     => $dedication->song->title ?? 'a song',
                    'recipient_name' => $dedication->to_name,
                    'message'        => $dedication->message,
                ],

                'is_read' => false,
            ]);

            $this->audit('dedication.approved', [
                'id' => $dedication->id
            ]);
        }

        return back()->with('status', 'Approved');
    }

    public function reject(Request $req, Dedication $dedication)
    {
        $req->validate(['reason'=>'nullable|string|max:255']);

        if ($dedication->status !== 'rejected') {
            $dedication->fill([
                'status'       => 'rejected',
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'reject_reason'=> $req->input('reason'),
            ])->save();

            $this->audit('dedication.rejected', ['id'=>$dedication->id, 'reason'=>$dedication->reject_reason]);
        }
        return back()->with('status','Rejected');
    }

    public function bulkApprove(Request $req)
    {
        $ids = (array) $req->input('ids', []);
        if (empty($ids)) return back()->with('status','No items selected');

        DB::table('dedications')->whereIn('id',$ids)->update([
            'status'=>'approved',
            'reviewed_by'=>auth()->id(),
            'reviewed_at'=>now(),
            'reject_reason'=>null,
        ]);

        $this->audit('dedication.bulk_approved', ['count'=>count($ids)]);
        return back()->with('status','Approved selected');
    }

    public function bulkReject(Request $req)
    {
        $req->validate(['reason'=>'nullable|string|max:255']);
        $ids = (array) $req->input('ids', []);
        if (empty($ids)) return back()->with('status','No items selected');

        DB::table('dedications')->whereIn('id',$ids)->update([
            'status'=>'rejected',
            'reviewed_by'=>auth()->id(),
            'reviewed_at'=>now(),
            'reject_reason'=>$req->input('reason'),
        ]);

        $this->audit('dedication.bulk_rejected', ['count'=>count($ids)]);
        return back()->with('status','Rejected selected');
    }

    private function audit(string $event, array $payload = []): void
    {
        DB::table('admin_audits')->insert([
            'admin_id'=>auth()->id(),
            'event'=>$event,
            'payload'=>json_encode($payload),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }
}
