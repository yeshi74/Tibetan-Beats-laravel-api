<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Albums
            </h2>
            <a href="{{ route('admin.albums.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700">
                + Create Album
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($albums->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">No albums found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="text-left text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700">
                                    <th class="px-4 py-2">#</th>
                                    <th class="px-4 py-2">Cover</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($albums as $index => $album)
                                    <tr class="border-b border-gray-200 dark:border-gray-600">
                                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2">
                                            @if ($album->cover_image)
                                                <img src="{{ asset('storage/' . $album->cover_image) }}" alt="Cover" class="h-16 w-16 object-cover rounded">
                                            @else
                                                <span class="text-sm text-gray-500">No image</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $album->name }}</td>
                                        <td class="px-4 py-2">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.albums.edit', $album->id) }}" class="text-indigo-600 hover:underline">Edit</a>
                                                <form action="{{ route('admin.albums.destroy', $album->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
