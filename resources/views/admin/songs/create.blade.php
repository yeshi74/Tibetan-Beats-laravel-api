<x-app-layout>
    <div class="max-w-2xl mx-auto py-10 px-6 bg-gray-900 text-white shadow rounded" x-data="uploadHandler()">
        <h2 class="text-2xl font-semibold mb-6">🎵 Upload New Song</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-800 text-red-200 rounded">
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
                <label class="block font-medium">Cover Image (optional)</label>
                <input type="file" name="cover_image" class="mt-1 block w-full text-gray-200" x-ref="cover">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Title</label>
                <input type="text" name="title" 
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                       required x-ref="title">
            </div>

            <div class="mb-4">
                <label class="block font-medium">Artist</label>
                <input type="text" name="artist" 
                       class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                       x-ref="artist">
            </div>

            <div class="mb-4">
                <label for="album_id" class="block font-medium">Select Album</label>
                <select name="album_id" id="album_id" 
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                    <option value="">-- None --</option>
                    @foreach($albums as $album)
                        <option value="{{ $album->id }}">{{ $album->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="genre" class="block font-medium">Genre</label>
                <select name="genre" id="genre" 
                        class="w-full bg-gray-800 text-white border border-gray-600 rounded px-4 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                        x-ref="genre" required>
                    <option value="">-- Select Genre --</option>
                    <option value="Losar">Losar</option>
                    <option value="Gorshey">Gorshey</option>
                    <option value="Rap">Rap</option>
                    <option value="Thungkar">Thungkar</option>
                    <option value="Love">Love</option>
                    <option value="Podcast">Podcast</option>
                    <option value="Phama">Phama</option>
                    <option value="Favourites">Favourites</option>
                    <option value="Wisdom of His Holiness">Wisdom of His Holiness</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium">MP3/WAV File</label>
                <input type="file" name="file" class="mt-1 block w-full text-gray-200" required x-ref="file">
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.songs.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-500">🎶 Upload Song</button>
            </div>

            <!-- Upload Progress -->
            <template x-if="isUploading">
                <div class="mt-4">
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" :style="`width: ${progress}%`"></div>
                    </div>
                    <div class="text-sm mt-1 text-gray-300" x-text="`${progress}% uploaded...`"></div>
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
                    formData.append('genre', this.$refs.genre.value);

                    this.isUploading = true;
                    this.progress = 0;

                    fetch('{{ route('admin.songs.store') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    }).then(response => {
                        this.isUploading = false;
                        if (!response.ok) throw new Error("Upload failed");
                        window.location.href = "{{ route('admin.songs.index') }}";
                    }).catch(error => {
                        alert("Upload error: " + error.message);
                        this.isUploading = false;
                    });

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
