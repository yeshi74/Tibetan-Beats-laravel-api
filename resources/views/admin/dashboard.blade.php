<x-app-layout>
    <div class="max-w-7xl mx-auto p-6">

        <h1 class="text-3xl font-bold mb-8">
            📊 Admin Dashboard
        </h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-gray-500 text-sm">Users</div>
                <div class="text-3xl font-bold mt-2">{{ $users }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-gray-500 text-sm">Songs</div>
                <div class="text-3xl font-bold mt-2">{{ $songs }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-gray-500 text-sm">Artists</div>
                <div class="text-3xl font-bold mt-2">{{ $artists }}</div>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <div class="text-gray-500 text-sm">Playlists</div>
                <div class="text-3xl font-bold mt-2">{{ $playlists }}</div>
            </div>

        </div>

    </div>
</x-app-layout>