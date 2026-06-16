<x-app-layout>

<div class="max-w-4xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">
            Create Dedication
        </h1>

        <a href="{{ route('admin.moderation.dedications.index') }}"
           class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
            Back
        </a>
    </div>

    <form method="POST"
          action="{{ route('admin.moderation.dedications.store') }}"
          class="bg-gray-800 rounded-lg p-6 space-y-5">

        @csrf

        <div>
            <label class="block text-gray-300 mb-2">
                User
            </label>

            <select name="user_id"
                    class="w-full rounded bg-gray-900 text-white border border-gray-700 p-2">
                @foreach($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-gray-300 mb-2">
                Song
            </label>

            <select name="song_id"
                    class="w-full rounded bg-gray-900 text-white border border-gray-700 p-2">
                @foreach($songs as $song)
                    <option value="{{ $song->id }}">
                        {{ $song->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-gray-300 mb-2">
                Dedicated To
            </label>

            <input type="text"
                   name="to_name"
                   class="w-full rounded bg-gray-900 text-black border border-gray-700 p-2">
        </div>

        <div>
            <label class="block text-gray-300 mb-2">
                Message
            </label>

            <textarea name="message"
                      rows="5"
                      class="w-full rounded bg-gray-900 text-white border border-gray-700 p-2"></textarea>
        </div>

        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">
            Create Dedication
        </button>

    </form>

</div>

</x-app-layout>