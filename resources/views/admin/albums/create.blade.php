<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Create Album') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg text-black">
                <form method="POST" action="{{ route('admin.albums.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Album Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Album Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            required
                            class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 text-black bg-white"
                        >
                    </div>

                    <!-- Cover Image -->
                    <div class="mb-4">
                        <label for="cover_image" class="block text-sm font-medium text-gray-700">Cover Image</label>
                        <input
                            type="file"
                            name="cover_image"
                            id="cover_image"
                            class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-black bg-white"
                        >
                    </div>


                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Create Album
                        </button>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('admin.albums.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-300">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
