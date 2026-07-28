<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">🎶 Uploaded Songs</h2>
            <a href="{{ route('admin.songs.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                + Upload New Song
            </a>
        </div>

        <!-- Success message -->
        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 shadow">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.songs.index') }}" class="mb-4 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search songs..."
                class="border rounded px-3 py-2 flex-1 min-w-[200px]">

            <!-- ✅ Dynamic Genres -->
            <select name="genre_id" class="border rounded px-3 py-2">
                <option value="">All Genres</option>
                @foreach ($genres as $g)
                    <option value="{{ $g->id }}" {{ request('genre_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                @endforeach
            </select>

            <select name="album_id" class="border rounded px-3 py-2">
                <option value="">All Albums</option>
                @foreach ($albums as $album)
                    <option value="{{ $album->id }}" {{ request('album_id') == $album->id ? 'selected' : '' }}>
                        {{ $album->name }}
                    </option>
                @endforeach
            </select>

            <select name="visibility" class="border rounded px-3 py-2">
                <option value="">All Visibility</option>

                <option value="1" {{ request('visibility') === '1' ? 'selected' : '' }}>
                    Visible
                </option>

                <option value="0" {{ request('visibility') === '0' ? 'selected' : '' }}>
                    Hidden
                </option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>

        </form>

        <div class="flex flex-wrap gap-4 mb-6">

            <div class="flex-1 min-w-[180px] bg-gray-900 rounded-lg p-4 shadow">
                <p class="text-gray-400 text-sm">Total Songs</p>
                <p class="text-3xl font-bold text-white">{{ $stats['total'] }}</p>
            </div>

            <div class="flex-1 min-w-[180px] bg-gray-700 rounded-lg p-4 shadow">
                <p class="text-gray-100 text-sm">Visible</p>
                <p class="text-3xl font-bold text-white">{{ $stats['visible'] }}</p>
            </div>

            <div class="flex-1 min-w-[180px] bg-gray-700 rounded-lg p-4 shadow">
                <p class="text-gray-200 text-sm">Hidden</p>
                <p class="text-3xl font-bold text-white">{{ $stats['hidden'] }}</p>
            </div>

            <div class="flex-1 min-w-[180px] bg-blue-700 rounded-lg p-4 shadow">
                <p class="text-blue-100 text-sm">Published</p>
                <p class="text-3xl font-bold text-white">{{ $stats['published'] }}</p>
            </div>

            <div class="flex-1 min-w-[180px] bg-yellow-600 rounded-lg p-4 shadow">
                <p class="text-yellow-100 text-sm">Pending</p>
                <p class="text-3xl font-bold text-white">{{ $stats['pending'] }}</p>
            </div>

        </div>



        <form id="bulk-form" action="{{ route('admin.songs.bulkVisibility') }}" method="POST"
            class="mb-6 bg-gray-800 rounded-lg p-4 flex items-center gap-3">
            @csrf
            @method('PATCH')

            <span class="text-white font-semibold">
                Bulk Actions
            </span>

            <button type="submit" name="is_visible" value="1" form="bulk-form"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
                onclick="return confirm('Show selected songs in the app?')">
                Show Selected
            </button>

            <button type="submit" name="is_visible" value="0" form="bulk-form"
                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded"
                onclick="return confirm('Hide selected songs from the app?')">
                Hide Selected
            </button>

            <span id="selected-count" class="ml-auto text-gray-300">
                0 selected
            </span>

        </form>

        <!-- Songs Table -->
        <div class="overflow-x-auto bg-gray-900 rounded-lg shadow-lg">
            <table class="table-auto w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-800 text-gray-200 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-gray-500">
                        </th>
                        <th class="px-4 py-3">Cover</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Artist</th>
                        <th class="px-4 py-3">Genre</th>
                        <th class="px-4 py-3">Album</th>
                        <th class="px-4 py-3">Audio</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Visibility</th>
                        <th>Moods</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($songs as $song)
                        <tr class="border-b border-gray-700 hover:bg-gray-800">

                            <td class="px-4 py-3">
                                <input type="checkbox" name="song_ids[]" value="{{ $song->id }}" form="bulk-form"
                                    class="song-checkbox rounded border-gray-500">
                            </td>
                            <!-- Cover -->
                            <td class="px-4 py-3">
                                @if ($song->cover_image)
                                    <img src="{{ asset('storage/' . $song->cover_image) }}"
                                        class="w-14 h-14 object-cover rounded-lg transform hover:scale-105 transition" />
                                @else
                                    <span class="text-gray-500">No Image</span>
                                @endif
                            </td>

                            <!-- Title -->
                            <td class="px-4 py-3 font-semibold text-white">{{ $song->title }}</td>

                            <!-- Artist -->
                            <td class="px-4 py-3">{{ $song->artist }}</td>

                            <!-- ✅ Genre (from DB relation) -->
                            <td class="px-4 py-3">
                                @if ($song->genre)
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-600 text-white">
                                        {{ $song->genre->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">No Genre</span>
                                @endif
                            </td>


                            <!-- Album -->
                            <td class="px-4 py-3">{{ $song->album->name ?? '-' }}</td>

                            <!-- Audio -->
                            <td class="px-4 py-3">
                                <audio controls class="w-44">
                                    <source src="{{ asset('storage/' . $song->file_path) }}" type="audio/mpeg">
                                </audio>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.songs.edit', $song) }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">Edit</a>

                                    <form action="{{ route('admin.songs.toggleVisibility', $song) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')

                                        @if ($song->is_visible)
                                            <button type="submit"
                                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs"
                                                onclick="return confirm('Hide this song from the mobile app?')">
                                                Hide
                                            </button>
                                        @else
                                            <button type="submit"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs"
                                                onclick="return confirm('Show this song in the mobile app?')">
                                                Show
                                            </button>
                                        @endif
                                    </form>

                                    <!-- ✅ Approve/Reject for pending songs -->
                                    @if ($song->status === 'pending')
                                        <form action="{{ route('admin.songs.approve', $song) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Approve this song?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">Approve</button>
                                        </form>

                                        <form action="{{ route('admin.songs.reject', $song) }}" method="POST"
                                            class="inline-flex gap-2 items-center">
                                            @csrf
                                            @method('PATCH')

                                            <input type="text" name="rejection_reason" placeholder="Reason..."
                                                required class="border rounded px-2 py-1 text-xs text-black w-40">

                                            <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs"
                                                onclick="return confirm('Reject this song?')">
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.songs.destroy', $song) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this song?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                @if ($song->status === 'pending')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded bg-yellow-600 text-white">Pending</span>
                                @elseif($song->status === 'published')
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded bg-green-600 text-white">Published</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded bg-red-600 text-white">Rejected</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if ($song->is_visible)
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-green-600 text-white">
                                        Visible
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-600 text-white">
                                        Hidden
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @forelse($song->moods as $mood)
                                    <span
                                        class="inline-block bg-gray-700 text-white text-xs px-2 py-1 rounded mr-1 mb-1">
                                        {{ $mood->name }}
                                    </span>
                                @empty
                                    <span class="text-gray-500">—</span>
                                @endforelse
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-500">No songs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $songs->links('pagination::tailwind') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const selectAll = document.getElementById('select-all');

            const checkboxes = document.querySelectorAll('.song-checkbox');

            const counter = document.getElementById('selected-count');

            function updateCounter() {
                const selected = document.querySelectorAll('.song-checkbox:checked').length;
                counter.textContent = `${selected} selected`;
            }

            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCounter();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCounter);
            });

        });
    </script>
</x-app-layout>
