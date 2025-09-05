<x-app-layout>
  <div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-6">Users</h1>

    @if(session('status'))
      <div class="mb-4 p-3 rounded bg-emerald-600/20 text-emerald-200">
        {{ session('status') }}
      </div>
    @endif

    {{-- Search & filters --}}
    <form method="get" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3 items-start">
      <input name="q" value="{{ $query ?? '' }}" placeholder="Search name or email"
             class="col-span-2 bg-gray-50 text-gray-800 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />

      <select name="role" class="bg-gray-50 text-gray-800 border border-gray-300 rounded px-3 py-2">
        <option value="">All roles</option>
        @foreach($roles as $r)
          <option value="{{ $r->name }}" {{ (string)($filter_role ?? '') === $r->name ? 'selected' : '' }}>
            {{ ucfirst($r->name) }}
          </option>
        @endforeach
      </select>

      <select name="banned" class="bg-gray-50 text-gray-800 border border-gray-300 rounded px-3 py-2">
        <option value="">All</option>
        <option value="1" {{ $filter_banned === '1' ? 'selected' : '' }}>Banned</option>
        <option value="0" {{ $filter_banned === '0' ? 'selected' : '' }}>Not banned</option>
      </select>

      <div class="sm:col-span-4 flex gap-2 mt-2">
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-600">Reset</a>
      </div>
    </form>

    {{-- users table --}}
    <div class="overflow-x-auto rounded-xl border border-gray-300 shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-800 text-gray-100 uppercase">
          <tr>
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">Role</th>
            <th class="px-4 py-3 text-left">Banned</th>
            <th class="px-4 py-3 text-left">Joined</th>
            <th class="px-4 py-3 text-left">Actions</th>
          </tr>
        </thead>

        {{-- Light body for contrast --}}
        <tbody class="bg-white divide-y divide-gray-200 text-gray-800">
          @foreach($users as $user)
            <tr>
              <td class="px-4 py-4 align-top">{{ $user->id }}</td>

              <td class="px-4 py-4">
                <div class="font-medium text-gray-900">{{ $user->name ?? '—' }}</div>
                @if($user->profile_image)
                  <div class="text-xs text-gray-500">has avatar</div>
                @endif
              </td>

              <td class="px-4 py-4 text-gray-700">{{ $user->email ?? '—' }}</td>

              <td class="px-4 py-4">
                @if(optional(auth()->user()->role)->name === 'admin')
                  <form method="post" action="{{ route('admin.users.updateRole', $user) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="role" class="bg-white text-gray-800 border border-gray-300 rounded px-2 py-1">
                      @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ optional($user->role)->name === $r->name ? 'selected' : '' }}>
                          {{ ucfirst($r->name) }}
                        </option>
                      @endforeach
                    </select>
                    <button class="px-2 py-1 rounded bg-sky-600 text-white hover:bg-sky-500">Save</button>
                  </form>
                @else
                  <span class="text-gray-700">{{ optional($user->role)->name ?? 'user' }}</span>
                @endif
              </td>

              <td class="px-4 py-4">
                @if($user->is_banned)
                  <span class="inline-block px-2 py-1 rounded text-xs bg-rose-100 text-rose-800 border border-rose-200">Banned</span>
                @else
                  <span class="inline-block px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                @endif
              </td>

              <td class="px-4 py-4 text-gray-600">{{ optional($user->created_at)->toDateString() }}</td>

              <td class="px-4 py-4 space-x-2">
                <a class="px-3 py-1 rounded bg-gray-100 text-gray-900 border border-gray-200" href="{{ route('admin.users.show', $user) }}">View</a>

                @if($user->trashed())
                  <form method="post" action="{{ route('admin.users.restore', $user->id) }}" class="inline">
                    @csrf
                    <button class="px-3 py-1 rounded bg-emerald-600 text-white">Restore</button>
                  </form>
                @else
                  @if($user->is_banned)
                    <form method="post" action="{{ route('admin.users.unban', $user) }}" class="inline">
                      @csrf @method('PATCH')
                      <button class="px-3 py-1 rounded bg-emerald-600 text-white">Unban</button>
                    </form>
                  @else
                    <form method="post" action="{{ route('admin.users.ban', $user) }}" class="inline" onsubmit="return confirm('Ban this user?');">
                      @csrf @method('PATCH')
                      <button class="px-3 py-1 rounded bg-rose-600 text-white">Ban</button>
                    </form>
                  @endif

                  <form method="post" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1 rounded bg-gray-100 text-gray-900 border border-gray-200">Delete</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6">
    {{ $users->appends(request()->query())->links('vendor.pagination.tailwind-dark') }}
  </div>
  </div>
</x-app-layout>
