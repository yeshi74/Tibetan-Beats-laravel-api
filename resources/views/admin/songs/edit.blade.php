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
                @if ($song->cover_image_url)
                    <img src="{{ $song->cover_image_url }}" alt="Cover" class="mt-2 h-20 rounded shadow">
                @endif
            </div>

            <div class="mb-4">
                <label class="block font-medium">Title</label>
                <input type="text" name="title" value="{{ old('title', $song->title) }}"
                    class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Artist</label>

                <select name="artist_id" class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2"
                    required>
                    <option value="">-- Select Artist --</option>

                    @foreach ($artists as $artist)
                        <option value="{{ $artist->id }}"
                            {{ old('artist_id', $song->artist_id) == $artist->id ? 'selected' : '' }}>
                            {{ $artist->display_name }}
                            @if (is_null($artist->user_id))
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
                    @foreach ($albums as $album)
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
                    class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2" required>
                    <option value="">-- Select Genre --</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->id }}"
                            {{ $genre->id == old('genre_id', $song->genre_id) ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Listen by Mood
                </label>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">

                    @foreach ($moods as $mood)
                        <label class="flex items-center gap-2 bg-gray-800 rounded px-3 py-2">

                            <input type="checkbox" name="moods[]" value="{{ $mood->id }}" class="rounded"
                                {{ in_array($mood->id, old('moods', $song->moods->pluck('id')->toArray())) ? 'checked' : '' }}>

                            <span>
                                {{ $mood->emoji }}
                                {{ $mood->name }}
                            </span>

                        </label>
                    @endforeach

                </div>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Start Color
                        </label>

                        <input type="color" name="start_color"
                            value="{{ old('start_color', $mood->start_color ?? '#8E24AA') }}"
                            class="w-full h-12 rounded-lg border">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">
                            End Color
                        </label>

                        <input type="color" name="end_color"
                            value="{{ old('end_color', $mood->end_color ?? '#AB47BC') }}"
                            class="w-full h-12 rounded-lg border">
                    </div>

                </div>

                <p class="text-sm text-gray-400 mt-2">
                    A song can belong to multiple moods.
                </p>

            </div>

            <div class="mb-4">
                <label for="is_visible" class="block font-medium">
                    Show in Mobile App
                </label>

                <select name="is_visible" id="is_visible"
                    class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2">
                    <option value="1" {{ old('is_visible', $song->is_visible) ? 'selected' : '' }}>
                        Yes
                    </option>

                    <option value="0" {{ !old('is_visible', $song->is_visible) ? 'selected' : '' }}>
                        No (Hidden)
                    </option>
                </select>

                <p class="text-sm text-gray-400 mt-1">
                    Hidden songs remain on the server but won't appear in the mobile app.
                </p>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Replace Audio (optional)</label>
                <input type="file" name="file" class="mt-1 block w-full text-gray-200">
                <p class="text-sm text-gray-400 mt-1">Current Audio: {{ $song->title }}</p>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.songs.index') }}"
                    class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-500">💾 Update
                    Song</button>
            </div>
        </form>
    </div>
</x-app-layout>
