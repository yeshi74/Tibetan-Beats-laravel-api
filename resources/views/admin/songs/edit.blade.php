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

        <div class="mb-4">
            <label class="block font-semibold">Replace Audio (optional)</label>
            <input type="file" name="file" class="block mt-1" />
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update Song</button>
    </form>
</x-app-layout>
