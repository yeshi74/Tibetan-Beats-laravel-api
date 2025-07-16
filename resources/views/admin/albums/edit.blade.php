<!-- resources/views/admin/albums/edit.blade.php -->

<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Album
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.albums.update', $album->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 dark:text-gray-300">Album Name</label>
                        <input id="name" name="name" type="text" value="{{ $album->name }}" required class="mt-1 block w-full rounded-md" />
                    </div>

                    <div class="mb-4">
                        <label for="cover_image" class="block text-gray-700 dark:text-gray-300">New Cover Image</label>
                        <input id="cover_image" name="cover_image" type="file" accept="image/*" class="mt-1 block w-full" />
                        @if ($album->cover_image)
                            <img src="{{ asset('storage/' . $album->cover_image) }}" alt="Cover" class="mt-2 h-24 w-24 object-cover rounded">
                        @endif
                    </div>

                    <div class="mt-4">
                        <label for="album_id" class="block font-medium text-sm text-gray-700">Select Album</label>
                        <select name="album_id" id="album_id" class="block mt-1 w-full">
                            <option value="">-- None --</option>
                            @foreach($albums as $album)
                                <option value="{{ $album->id }}" {{ old('album_id', $song->album_id ?? '') == $album->id ? 'selected' : '' }}>
                                    {{ $album->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>Update</x-primary-button>
                        <a href="{{ route('admin.albums.index') }}" class="ml-4 text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
