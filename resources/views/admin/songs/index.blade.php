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
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="🔍 Search songs..." 
                   class="border rounded px-3 py-2 flex-1 min-w-[200px]">

            <select name="genre" class="border rounded px-3 py-2">
                <option value="">All Genres</option>
                @foreach(['Love','Gorshey','Rap','Thungkar','Phama','Podcast','Losar','Favourites','Wisdom of His Holiness'] as $g)
                    <option value="{{ $g }}" {{ request('genre') == $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>

            <select name="album_id" class="border rounded px-3 py-2">
                <option value="">All Albums</option>
                @foreach($albums as $album)
                    <option value="{{ $album->id }}" {{ request('album_id') == $album->id ? 'selected' : '' }}>
                        {{ $album->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
        </form>

        <!-- Songs Table -->
        <div class="overflow-x-auto bg-gray-900 rounded-lg shadow-lg">
            <table class="table-auto w-full text-sm text-left text-gray-300">
                <thead class="bg-gray-800 text-gray-200 sticky top-0">
                    <tr>
                        <th class="px-4 py-3">Cover</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Artist</th>
                        <th class="px-4 py-3">Genre</th>
                        <th class="px-4 py-3">Album</th>
                        <th class="px-4 py-3">Audio</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($songs as $song)
                        <tr class="border-b border-gray-700 hover:bg-gray-800">
                            <!-- Cover -->
                            <td class="px-4 py-3">
                                @if($song->cover_image)
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

                            <!-- Genre Badge -->
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $song->genre == 'Love' ? 'bg-pink-600 text-white' : 
                                       ($song->genre == 'Rap' ? 'bg-purple-600 text-white' : 
                                       'bg-gray-600 text-gray-200') }}">
                                    {{ $song->genre ?? '-' }}
                                </span>
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
                                    <form action="{{ route('admin.songs.destroy', $song) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this song?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No songs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $songs->links() }}
        </div>
    </div>
</x-app-layout>
