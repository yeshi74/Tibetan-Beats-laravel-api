<x-app-layout>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-4">User #{{ $user->id }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="col-span-1">
        <div class="bg-gray-900 p-4 rounded text-center">
          @if($user->profile_image)
            <img src="{{ asset('storage/'.$user->profile_image) }}" alt="" class="mx-auto w-32 h-32 rounded-full object-cover" />
          @else
            <div class="w-32 h-32 rounded-full bg-gray-700 mx-auto flex items-center justify-center">
              <span class="text-xl text-gray-300">{{ strtoupper(substr($user->name ?? 'U',0,1)) }}</span>
            </div>
          @endif
          <div class="mt-4">
            <div class="font-semibold text-gray-100">{{ $user->name }}</div>
            <div class="text-sm text-gray-400">{{ $user->email }}</div>
          </div>
        </div>
      </div>

      <div class="col-span-2">
        <div class="bg-gray-900 p-4 rounded space-y-3">
          <div>
            <strong>Role:</strong> {{ optional($user->role)->name ?? 'user' }}
          </div>
          <div>
            <strong>Banned:</strong> {{ $user->is_banned ? 'Yes' : 'No' }}
          </div>
          <div>
            <strong>Joined:</strong> {{ optional($user->created_at)->toDayDateTimeString() }}
          </div>

          <div class="mt-4 space-x-2">
            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded bg-gray-700 text-white">Back</a>
            @if(!$user->is_banned)
              <form method="post" action="{{ route('admin.users.ban', $user) }}" class="inline">
                @csrf @method('PATCH')
                <button class="px-3 py-2 rounded bg-rose-600 text-white">Ban</button>
              </form>
            @else
              <form method="post" action="{{ route('admin.users.unban', $user) }}" class="inline">
                @csrf @method('PATCH')
                <button class="px-3 py-2 rounded bg-emerald-600 text-white">Unban</button>
              </form>
            @endif
            <form method="post" action="{{ url('admin/users/'.$user->id) }}" class="inline" onsubmit="return confirm('Delete user?')">
              @csrf @method('DELETE')
              <button class="px-3 py-2 rounded bg-gray-700 text-white">Delete</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
