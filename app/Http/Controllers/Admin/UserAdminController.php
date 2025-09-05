<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserAdminController extends Controller
{
    public function index(Request $req)
    {
        $q = User::query()->with('role')->withTrashed();

        if ($s = $req->get('q')) {
            $q->where(fn($w) => $w->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
        }

        if ($role = $req->get('role')) {
            // allow either role name or role id
            if (is_numeric($role)) {
                $q->whereHas('role', fn($w) => $w->where('id', (int)$role));
            } else {
                $q->whereHas('role', fn($w) => $w->where('name', $role));
            }
        }

        if ($req->filled('banned')) {
            // support ?banned=1 or banned=0
            $b = (bool) $req->boolean('banned');
            $q->where('is_banned', $b);
        }

        return view('admin.users.index', [
            'users' => $q->orderBy('created_at','desc')->paginate(25)->withQueryString(),
            'roles' => Role::orderBy('name')->get(),
            'query' => $req->get('q', ''),
            'filter_role' => $req->get('role', ''),
            'filter_banned' => $req->get('banned', ''),
        ]);
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user->load('role')
        ]);
    }

    public function ban(User $user)
    {
        if (auth()->id() === $user->id) abort(403, 'Cannot ban yourself');
        $user->is_banned = true;
        $user->save();

        $this->audit('user.banned', ['user_id' => $user->id]);
        return back()->with('status', 'User banned');
    }

    public function unban(User $user)
    {
        $user->is_banned = false;
        $user->save();

        $this->audit('user.unbanned', ['user_id' => $user->id]);
        return back()->with('status', 'User unbanned');
    }

    public function updateRole(Request $req, User $user)
    {
        // Build allowed list from Role table to avoid hardcoding
        $allowed = Role::pluck('name')->map(fn($n) => (string)$n)->toArray();

        $req->validate(['role' => ['required','string', 'in:'.implode(',', $allowed) ]]);

        if (auth()->id() === $user->id) abort(403, 'Cannot change your own role');

        $role = Role::where('name', $req->role)->firstOrFail();
        $user->role()->associate($role);
        $user->save();

        // Optional: keep is_admin column in sync if your users table still has it
        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = ($role->name === 'admin');
            $user->save();
        }

        $this->audit('user.role.updated', ['user_id' => $user->id, 'role' => $role->name]);
        return back()->with('status', 'Role updated');
    }

    public function softDelete(User $user)
    {
        if (auth()->id() === $user->id) abort(403, 'Cannot delete yourself');
        $user->delete();
        $this->audit('user.deleted',['user_id' => $user->id]);
        return back()->with('status','User soft-deleted');
    }

    public function restore($id)
    {
        $u = User::withTrashed()->findOrFail($id);
        $u->restore();
        $this->audit('user.restored',['user_id' => $u->id]);
        return back()->with('status','User restored');
    }

    private function audit(string $event, array $payload = [])
    {
        DB::table('admin_audits')->insert([
            'admin_id' => auth()->id(),
            'event'    => $event,
            'payload'  => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
