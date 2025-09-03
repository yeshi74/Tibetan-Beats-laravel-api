<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-white">✏️ Edit Song</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 px-6 bg-gray-900 text-white shadow rounded">
        <form method="POST" action="{{ route('admin.songs.update', $song) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if($song->cover_image)
                <img src="{{ asset('storage/' . $song->cover_image) }}" 
                     alt="Cover Image" 
                     class="w-24 h-24 object-cover mb-4 rounded shadow" />
            @endif

            <div class="mb-4">
                <label class="block font-semibold">Replace Cover Image</label>
                <input type="file" name="cover_image" class="mt-1 block w-full text-gray-200">
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Title</label>
                <input name="title" value="{{ old('title', $song->title) }}"
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500" />
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Artist</label>
                <input name="artist" value="{{ old('artist', $song->artist) }}"
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500" />
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Album</label>
                <select name="album_id"
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">-- None --</option>
                    @foreach($albums as $album)
                        <option value="{{ $album->id }}" 
                            {{ old('album_id', $song->album_id) == $album->id ? 'selected' : '' }}>
                            {{ $album->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Genre</label>
                <select name="genre" required
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">-- Select Genre --</option>
                    <option value="Love" {{ old('genre', $song->genre) == 'Love' ? 'selected' : '' }}>Love</option>
                    <option value="Gorshey" {{ old('genre', $song->genre) == 'Gorshey' ? 'selected' : '' }}>Gorshey</option>
                    <option value="Rap" {{ old('genre', $song->genre) == 'Rap' ? 'selected' : '' }}>Rap</option>
                    <option value="Thungkar" {{ old('genre', $song->genre) == 'Thungkar' ? 'selected' : '' }}>Thungkar</option>
                    <option value="Phama" {{ old('genre', $song->genre) == 'Phama' ? 'selected' : '' }}>Phama</option>
                    <option value="Podcast" {{ old('genre', $song->genre) == 'Podcast' ? 'selected' : '' }}>Podcast</option>
                    <option value="Losar" {{ old('genre', $song->genre) == 'Losar' ? 'selected' : '' }}>Losar</option>
                    <option value="Favourites" {{ old('genre', $song->genre) == 'Favourites' ? 'selected' : '' }}>Favourites</option>
                    <option value="Wisdom of His Holiness" {{ old('genre', $song->genre) == 'Wisdom of His Holiness' ? 'selected' : '' }}>Wisdom of His Holiness</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Replace Audio (optional)</label>
                <input type="file" name="file" class="block mt-1 text-gray-200" />
            </div>

            <div class="mb-4">
                <label class="block font-semibold">Current Audio</label>
                <audio controls class="w-full">
                    <source src="{{ asset('storage/' . $song->file_path) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.songs.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-yellow-500 text-black font-semibold rounded hover:bg-yellow-400">💾 Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
