<x-app-layout>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-100">Notifications</h1>
      <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">New</a>
    </div>

    <form method="get" class="mb-4">
      <input name="q" value="{{ $query ?? '' }}" placeholder="Search title or body" class="w-full sm:w-96 bg-gray-800 text-gray-100 rounded px-3 py-2" />
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-700">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-800 text-gray-300">
          <tr>
            <th class="px-4 py-3 text-left">Title</th>
            <th class="px-4 py-3 text-left">Audience</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Sent</th>
            <th class="px-4 py-3">Created</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-gray-900 divide-y divide-gray-800 text-gray-100">
          @foreach($notifications as $n)
            <tr>
              <td class="px-4 py-3">
                <div class="font-semibold">{{ $n->title }}</div>
                <div class="text-xs text-gray-400">{{ Str::limit($n->body, 100) }}</div>
              </td>
              <td class="px-4 py-3">{{ $n->audience }}</td>
              <td class="px-4 py-3">{{ $n->type ?? '—' }}</td>
              <td class="px-4 py-3">{{ $n->sent_count }}</td>
              <td class="px-4 py-3">{{ optional($n->created_at)->toDayDateTimeString() }}</td>
              <td class="px-4 py-3">
                <a href="{{ route('admin.notifications.show', $n) }}" class="px-2 py-1 rounded bg-gray-700 text-white">View</a>
                <form method="post" action="{{ route('admin.notifications.destroy',$n) }}" class="inline">
                  @csrf @method('DELETE')
                  <button class="px-2 py-1 rounded bg-rose-600 text-white">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $notifications->links() }}
    </div>
  </div>
</x-app-layout>
