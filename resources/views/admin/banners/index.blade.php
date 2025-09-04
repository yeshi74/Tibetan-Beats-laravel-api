<x-app-layout>
  <div class="max-w-6xl mx-auto py-8 px-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-white">Hero Banners</h2>
      <a href="{{ route('admin.banners.create') }}"
         class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">+ New Banner</a>
    </div>

    @if(session('success'))
      <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
        {{ session('success') }}
      </div>
    @endif

    <div class="overflow-x-auto bg-gray-900 rounded-lg shadow">
      <table class="table-auto w-full text-left text-gray-200">
        <thead class="bg-gray-800">
          <tr>
            <th class="px-4 py-3">Preview</th>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">CTA</th>
            <th class="px-4 py-3">Window</th>
            <th class="px-4 py-3">Active</th>
            <th class="px-4 py-3">Pos</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($banners as $b)
            <tr class="border-b border-gray-700">
              <td class="px-4 py-3">
                <img src="{{ $b->image_url }}" class="w-40 h-20 object-cover rounded" />
              </td>
              <td class="px-4 py-3 font-semibold">{{ $b->title ?? '—' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs rounded bg-gray-700">{{ strtoupper($b->cta_type) }}</span>
                @if($b->cta_type === 'url' && $b->cta_url)
                    <div class="text-xs text-gray-400 truncate w-56">{{ $b->cta_url }}</div>
                @elseif(in_array($b->cta_type, ['song','playlist','album']) && $b->cta_id)
                    <div class="text-xs text-gray-400">ID: {{ $b->cta_id }}</div>
                @endif
              </td>
              <td class="px-4 py-3 text-sm">
                <div>{{ $b->starts_at?->format('Y-m-d H:i') ?? '—' }} → {{ $b->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <form method="POST" action="{{ route('admin.banners.toggle', $b) }}">
                  @csrf
                  <button class="px-3 py-1 rounded {{ $b->is_active ? 'bg-green-600' : 'bg-gray-600' }}">
                    {{ $b->is_active ? 'Active' : 'Inactive' }}
                  </button>
                </form>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <span class="text-sm">{{ $b->position }}</span>
                  <form method="POST" action="{{ route('admin.banners.move', $b) }}">
                    @csrf
                    <input type="hidden" name="direction" value="up">
                    <button class="px-2 py-1 bg-gray-700 rounded">↑</button>
                  </form>
                  <form method="POST" action="{{ route('admin.banners.move', $b) }}">
                    @csrf
                    <input type="hidden" name="direction" value="down">
                    <button class="px-2 py-1 bg-gray-700 rounded">↓</button>
                  </form>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center gap-2">
                  <a href="{{ route('admin.banners.edit', $b) }}"
                     class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">Edit</a>
                  <form method="POST" action="{{ route('admin.banners.destroy', $b) }}"
                        onsubmit="return confirm('Delete this banner?')">
                    @csrf @method('DELETE')
                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No banners yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $banners->links() }}
    </div>
  </div>
</x-app-layout>
