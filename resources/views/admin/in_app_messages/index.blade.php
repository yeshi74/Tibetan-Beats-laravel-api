<x-app-layout>
  {{-- make the page content area light while leaving global layout intact --}}
  <div class="max-w-6xl mx-auto py-10 px-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold text-gray-900">In-App Messages</h1>
      <a href="{{ route('admin.in_app_messages.create') }}"
         class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow">
        New Message
      </a>
    </div>

    {{-- Search --}}
    <form method="get" class="mb-6">
      <input type="search" name="q" placeholder="Search key/title/body"
             value="{{ request('q') }}"
             class="w-full rounded-md border px-4 py-3 bg-white text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-300" />
    </form>

    {{-- Page container: light card --}}
    <div class="bg-white border rounded-lg shadow p-4">
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto text-left">
          <thead>
            <tr class="border-b">
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Key</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Type</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Title</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Audience</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Priority</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700">Status</th>
              <th class="px-6 py-3 text-sm font-medium text-gray-700 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($messages as $m)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 align-top text-sm text-gray-800 break-words">{{ $m->key ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($m->type) }}</td>
                <td class="px-6 py-4 text-sm text-gray-800">{{ $m->title['en'] ?? $m->title }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ $m->audience ?? 'all' }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ $m->priority ?? '0' }}</td>
                <td class="px-6 py-4 text-sm">
                  @if($m->status === 'active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs">Active</span>
                  @elseif($m->status === 'draft')
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs">Draft</span>
                  @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 text-xs">Archived</span>
                  @endif
                </td>

                <td class="px-6 py-4 text-right space-x-2">
                  <a href="{{ route('admin.in_app_messages.show', $m) }}"
                     class="inline-block px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded border">View</a>

                  <a href="{{ route('admin.in_app_messages.edit', $m) }}"
                     class="inline-block px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded">Edit</a>

                  <form action="{{ route('admin.in_app_messages.destroy', $m) }}" method="post" class="inline-block" onsubmit="return confirm('Delete this message?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                  No messages yet. <a href="{{ route('admin.in_app_messages.create') }}" class="text-indigo-600">Create one</a>.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- pagination --}}
      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Showing {{ $messages->firstItem() ?? 0 }}–{{ $messages->lastItem() ?? 0 }} of {{ $messages->total() }}
        </div>
        <div>
          {{ $messages->withQueryString()->links() }}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
