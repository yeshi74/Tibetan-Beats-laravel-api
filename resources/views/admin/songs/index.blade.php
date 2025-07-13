<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Uploaded Songs
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded my-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="text-right mb-4">
        <a href="{{ route('admin.songs.create') }}" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded">
            + Upload New Song
        </a>
    </div>

    <table class="table-auto w-full border mt-6">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="px-4 py-2">Cover</th>
            <th class="px-4 py-2">Title</th>
            <th class="px-4 py-2">Artist</th>
            <th class="px-4 py-2">Audio</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($songs as $song)
        <tr class="border-b bg-white text-black">
            <td class="px-4 py-2">
                @if($song->cover_image)
                    <img src="{{ asset('storage/' . $song->cover_image) }}" alt="Cover" class="w-16 h-16 object-cover rounded" />
                @else
                    <span class="text-gray-500">No Image</span>
                @endif
            </td>
            <td class="px-4 py-2">{{ $song->title }}</td>
            <td class="px-4 py-2">{{ $song->artist }}</td>
            <td class="px-4 py-2">
                <audio controls class="w-48">
                    <source src="{{ asset('storage/' . $song->file_path) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </td>
            <td class="px-4 py-2 space-x-2">
                <a href="{{ route('admin.songs.edit', $song) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Edit</a>
                <form action="{{ route('admin.songs.destroy', $song) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</x-app-layout>
