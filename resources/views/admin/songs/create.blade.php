<x-app-layout>
    <div class="max-w-2xl mx-auto py-10 px-6 bg-gray-900 text-white shadow rounded">
        <h2 class="text-2xl font-semibold mb-6">🎵 Upload New Song</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-800 text-red-200 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.songs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block font-medium">Cover Image (optional)</label>
                <input type="file" name="cover_image" class="mt-1 block w-full text-gray-200">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Title</label>
                <input type="text" name="title" 
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                       required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Artist</label>

                <select
                    name="artist_id"
                    class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                    required
                >
                    <option value="">-- Select Artist --</option>

                    @foreach($artists as $artist)
                        <option value="{{ $artist->id }}">
                            {{ $artist->display_name }}
                            @if(is_null($artist->user_id))
                                (Unclaimed)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="album_id" class="block font-medium">Select Album</label>
                <select name="album_id" id="album_id" 
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2">
                    <option value="">-- None --</option>
                    @foreach($albums as $album)
                        <option value="{{ $album->id }}">{{ $album->name }}</option>
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
                        <option value="{{ $genre->id }}">{{ $genre->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="is_visible" class="block font-medium">
                    Show in Mobile App
                </label>

                <select
                    name="is_visible"
                    id="is_visible"
                    class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                >
                    <option value="1" selected>Yes</option>
                    <option value="0">No (Hidden)</option>
                </select>

                <p class="text-sm text-gray-400 mt-1">
                    Hidden songs are stored in the system but won't appear in the mobile app.
                </p>
            </div>

            <div class="mb-4">
                <label class="block font-medium">MP3/WAV File</label>
                <input type="file" name="file" class="mt-1 block w-full text-gray-200" required>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.songs.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-500">🎶 Upload Song</button>
            </div>
        </form>
    </div>
</x-app-layout>
