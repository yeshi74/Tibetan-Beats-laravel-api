<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold">Edit Song</h2>
    </x-slot>

    <form method="POST" action="{{ route('admin.songs.update', $song) }}" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        @if($song->cover_image)
            <img src="{{ asset('storage/' . $song->cover_image) }}" alt="Cover Image" class="w-24 h-24 object-cover mb-2" />
        @endif

        <div class="mb-4">
            <label class="block font-semibold">Title</label>
            <input name="title" value="{{ old('title', $song->title) }}" class="w-full border px-4 py-2 rounded" />
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Artist</label>
            <input name="artist" value="{{ old('artist', $song->artist) }}" class="w-full border px-4 py-2 rounded" />
        </div>

        <div class="mt-4">
            <label for="album_id" class="block font-medium text-sm text-gray-700">Select Album</label>
            <select name="album_id" id="album_id" class="block mt-1 w-full border px-4 py-2 rounded">
                <option value="">-- None --</option>
                @foreach($albums as $album)
                    <option value="{{ $album->id }}" {{ old('album_id', $song->album_id ?? '') == $album->id ? 'selected' : '' }}>
                        {{ $album->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- 🎶 Genre Dropdown -->
        <div class="mt-4">
            <label for="genre" class="block font-medium text-sm text-gray-700">Genre</label>
            <select name="genre" id="genre" class="block mt-1 w-full border px-4 py-2 rounded" required>
                <option value="">-- Select Genre --</option>
                <option value="Love" {{ old('genre', $song->genre) == 'Love' ? 'selected' : '' }}>Love</option>
                <option value="Gorshey" {{ old('genre', $song->genre) == 'Gorshey' ? 'selected' : '' }}>Gorshey</option>
                <option value="Rap" {{ old('genre', $song->genre) == 'Rap' ? 'selected' : '' }}>Rap</option>
                <option value="Thungkar" {{ old('genre', $song->genre) == 'Thungkar' ? 'selected' : '' }}>Thungkar</option>
                <option value="Classical" {{ old('genre', $song->genre) == 'Classical' ? 'selected' : '' }}>Classical</option>
                <option value="Podcast" {{ old('genre', $song->genre) == 'Podcast' ? 'selected' : '' }}>Podcast</option>
                <option value="Losar" {{ old('genre', $song->genre) == 'Losar' ? 'selected' : '' }}>Losar</option>
            </select>
        </div>

        <div class="mb-4 mt-4">
            <label class="block font-semibold">Replace Audio (optional)</label>
            <input type="file" name="file" class="block mt-1" />
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Song</button>
    </form>
</x-app-layout>
