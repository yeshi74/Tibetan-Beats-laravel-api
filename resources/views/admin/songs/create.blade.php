<x-app-layout>
    <div class="max-w-2xl mx-auto py-10 px-6 bg-white shadow rounded" x-data="uploadHandler()">
        <h2 class="text-2xl font-semibold mb-6">🎵 Upload New Song</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.songs.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm($event)">
            @csrf

            <div class="mb-4">
                <label class="block font-medium text-gray-700">Cover Image (optional)</label>
                <input type="file" name="cover_image" class="mt-1 block w-full" x-ref="cover">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700">Title</label>
                <input type="text" name="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required x-ref="title">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700">Artist</label>
                <input type="text" name="artist" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" x-ref="artist">
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

            <!-- 🎶 Genre Dropdown -->
            <div class="mt-4">
                <label for="genre" class="block font-medium text-sm text-gray-700">Genre</label>
                <select name="genre" id="genre" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-ref="genre" required>
                    <option value="">-- Select Genre --</option>
                    <option value="Pop">Losar</option>
                    <option value="Rock">Gorshey</option>
                    <option value="Hip-Hop">Rap</option>
                    <option value="Jazz">Thungkar</option>
                    <option value="Classical">Classical</option>
                    <option value="Folk">Podcast</option>
                    <option value="Electronic">Electronic</option>
                </select>
            </div>

            <div class="mb-4 mt-4">
                <label class="block font-medium text-gray-700">MP3/WAV File</label>
                <input type="file" name="file" class="mt-1 block w-full" required x-ref="file">
            </div>

            <div class="mb-4">
                <button type="submit" class="bg-gray-800 hover:bg-green-500 text-white px-6 py-2 rounded">
                    🎶 Upload Song
                </button>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <a href="{{ route('admin.songs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-300">
                    Cancel
                </a>
            </div>

            <!-- Upload Progress -->
            <template x-if="isUploading">
                <div class="mb-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" :style="`width: ${progress}%`"></div>
                    </div>
                    <div class="text-sm mt-1 text-gray-600" x-text="`${progress}% uploaded...`"></div>
                </div>
            </template>
        </form>
    </div>

    <script>
        function uploadHandler() {
            return {
                isUploading: false,
                progress: 0,

                submitForm(e) {
                    const formData = new FormData();

                    formData.append('cover_image', this.$refs.cover.files[0] || '');
                    formData.append('title', this.$refs.title.value);
                    formData.append('artist', this.$refs.artist.value);
                    formData.append('file', this.$refs.file.files[0]);

                    const albumSelect = document.getElementById('album_id');
                    formData.append('album_id', albumSelect.value);

                    // ✅ Add Genre
                    formData.append('genre', this.$refs.genre.value);

                    this.isUploading = true;
                    this.progress = 0;

                    fetch('{{ route('admin.songs.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    }).then(response => {
                        this.isUploading = false;
                        if (!response.ok) throw new Error("Upload failed");
                        window.location.href = "{{ route('admin.songs.index') }}";
                    }).catch(error => {
                        alert("Upload error: " + error.message);
                        this.isUploading = false;
                    });

                    // Simulated progress
                    const interval = setInterval(() => {
                        if (this.progress < 90) {
                            this.progress += 5;
                        } else {
                            clearInterval(interval);
                        }
                    }, 200);
                }
            };
        }
    </script>
</x-app-layout>
