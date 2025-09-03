<x-app-layout>
    <div class="max-w-2xl mx-auto py-10 px-6 bg-gray-900 text-white shadow rounded">
        <h2 class="text-2xl font-semibold mb-6">✏️ Edit Song</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-800 text-red-200 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.songs.update', $song->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block font-medium">Replace Cover Image</label>
                <input type="file" name="cover_image" class="mt-1 block w-full text-gray-200">
                @if($song->cover_image_url)
                    <img src="{{ $song->cover_image_url }}" alt="Cover" class="mt-2 h-20 rounded shadow">
                @endif
            </div>

            <div class="mb-4">
                <label class="block font-medium">Title</label>
                <input type="text" name="title" value="{{ old('title', $song->title) }}"
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                       required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Artist</label>
                <input type="text" name="artist" value="{{ old('artist', $song->artist) }}"
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2">
            </div>

            <div class="mb-4">
                <label for="album_id" class="block font-medium">Select Album</label>
                <select name="album_id" id="album_id" 
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2">
                    <option value="">-- None --</option>
                    @foreach($albums as $album)
                        <option value="{{ $album->id }}" 
                            {{ $album->id == old('album_id', $song->album_id) ? 'selected' : '' }}>
                            {{ $album->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="genre_id" class="block font-medium">Genre</label>
                <select name="genre_id" id="genre_id" 
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                        required>
                    <option value="">-- Select Genre --</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre->id }}" 
                            {{ $genre->id == old('genre_id', $song->genre_id) ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Replace Audio (optional)</label>
                <input type="file" name="file" class="mt-1 block w-full text-gray-200">
                <p class="text-sm text-gray-400 mt-1">Current Audio: {{ $song->title }}</p>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.songs.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-500">💾 Update Song</button>
            </div>
        </form>
    </div>
</x-app-layout>
