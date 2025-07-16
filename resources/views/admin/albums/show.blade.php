<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Album: {{ $album->name }}
        </h2>
    </x-slot>

    <div class="py-4 px-6">
        <div class="mb-6">
            <img src="{{ asset('storage/' . $album->cover_image) }}" alt="Album Cover" class="w-48 h-48 object-cover rounded">
        </div>

        <h3 class="text-lg font-bold mb-2">Songs in this album:</h3>
        @if($album->songs->isEmpty())
            <p class="text-gray-500">No songs assigned to this album yet.</p>
        @else
            <ul class="space-y-2">
                @foreach($album->songs as $song)
                    <li class="p-4 bg-white rounded shadow">
                        <strong>{{ $song->title }}</strong> by {{ $song->artist }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
