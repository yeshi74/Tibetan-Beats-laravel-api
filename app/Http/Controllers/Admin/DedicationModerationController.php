<?php

namespace App\Http\Controllers\Admin;
use App\Models\Dedication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\DedicationComment;
use App\Models\User;
use App\Models\Song;

class DedicationModerationController extends Controller
{
    public function index(\Illuminate\Http\Request $req)
    {
        $status = $req->string('status')->toString();

        $q = \App\Models\Dedication::query()
            ->with(['reviewer','user','song'])
            ->withCount('comments')
            ->latest();

        if ($status !== '') {
            $q->where('status', $status);
        }

        $search = trim($req->get('search', ''));

        if ($search !== '') {

            $q->where(function ($query) use ($search) {

                $query->where('to_name', 'like', "%{$search}%")

                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('song', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });

            });
        }

        $stats = [
            'pending' => Dedication::pending()->count(),
            'approved' => Dedication::approved()->count(),
            'rejected' => Dedication::rejected()->count(),
            'comments' => \App\Models\DedicationComment::count(),
        ];

        return view('admin.moderation.dedications.index', [
            'dedications' => $q->paginate(30)->withQueryString(),
            'status'      => $status,
            'search'      => $search,
            'stats'       => $stats,
        ]);
    }

    public function show(Dedication $dedication)
    {
        return view('admin.moderation.dedications.show', [
            'd' => $dedication
                ->load([
                    'reviewer',
                    'user',
                    'song',
                    'toUser',
                ])
                ->loadCount([
                    'comments',
                    'likes',
                ]),
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

    public function destroy(Dedication $dedication)
    {
        $dedication->comments()->delete();
        $dedication->likes()->delete();

        $dedication->delete();

        return back()->with(
            'success',
            'Dedication deleted successfully.'
        );
    }

    public function comments(Dedication $dedication)
    {
        $comments = $dedication->comments()
            ->with('user')
            ->latest()
            ->paginate(50);

        return view(
            'admin.moderation.dedications.comments',
            compact('dedication', 'comments')
        );
    }

    public function destroyComment(DedicationComment $comment)
    {
        $comment->delete();

        return back()->with(
            'success',
            'Comment deleted successfully.'
        );
    }

    public function create()
    {
        return view(
            'admin.moderation.dedications.create',
            [
                'users' => User::orderBy('name')->get(),
                'songs' => Song::orderBy('title')->get(),
            ]
        );
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'song_id' => ['required', 'exists:songs,id'],
            'to_name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:300'],
        ]);

        Dedication::create([
            'user_id' => $data['user_id'],
            'song_id' => $data['song_id'],
            'to_name' => $data['to_name'],
            'message' => $data['message'],

            'status' => 'approved',

            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reject_reason' => null,
        ]);

        return redirect()
            ->route('admin.moderation.dedications.index')
            ->with('status', 'Dedication created successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with(
                'error',
                'No dedications selected.'
            );
        }

        $dedications = Dedication::whereIn('id', $ids)->get();

        foreach ($dedications as $dedication) {

            $dedication->comments()->delete();

            $dedication->likes()->delete();

            $dedication->delete();
        }

        return back()->with(
            'status',
            $ids->count().' dedication(s) deleted.'
        );
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
