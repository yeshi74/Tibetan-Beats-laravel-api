<x-app-layout>
  <div class="max-w-2xl mx-auto py-8 px-6 bg-gray-900 rounded">
    <h2 class="text-xl font-bold text-white mb-4">Edit Banner</h2>

    @if ($errors->any())
      <div class="mb-4 p-3 rounded bg-red-900 text-red-100">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-4">
      @csrf @method('PUT')

      <div>
        <label class="block text-gray-300 mb-1">Title (optional)</label>
        <input name="title" value="{{ old('title',$banner->title) }}" class="w-full bg-gray-800 text-white rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-gray-300 mb-1">Image</label>
        @if($banner->image_url)
          <img src="{{ $banner->image_url }}" class="w-full h-36 object-cover rounded mb-2" />
        @endif
        <input type="file" name="image" accept="image/*" class="w-full text-gray-200">
      </div>

      <div>
        <label class="block text-gray-300 mb-1">CTA Type</label>
        <select name="cta_type" id="cta_type" class="w-full bg-gray-800 text-white rounded px-3 py-2">
          @foreach(['none','song','playlist','album','url'] as $t)
            <option value="{{ $t }}" {{ $banner->cta_type===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
          @endforeach
        </select>
      </div>

      <div id="cta_id_wrap" style="display:none;">
        <label class="block text-gray-300 mb-1">CTA ID (for Song/Playlist/Album)</label>
        <input name="cta_id" type="number" value="{{ old('cta_id',$banner->cta_id) }}" class="w-full bg-gray-800 text-white rounded px-3 py-2">
      </div>

      <div id="cta_url_wrap" style="display:none;">
        <label class="block text-gray-300 mb-1">CTA URL</label>
        <input name="cta_url" type="url" value="{{ old('cta_url',$banner->cta_url) }}" class="w-full bg-gray-800 text-white rounded px-3 py-2">
      </div>

      <div class="mb-3 form-check">
        <input type="hidden" name="is_active" value="0"> <!-- ensures it always sends -->
        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
               {{ old('is_active', $banner->is_active ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active</label>
    </div>


      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-gray-300 mb-1">Starts At</label>
          <input type="datetime-local" name="starts_at"
                 value="{{ optional($banner->starts_at)->format('Y-m-d\TH:i') }}"
                 class="w-full bg-gray-800 text-white rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-gray-300 mb-1">Ends At</label>
          <input type="datetime-local" name="ends_at"
                 value="{{ optional($banner->ends_at)->format('Y-m-d\TH:i') }}"
                 class="w-full bg-gray-800 text-white rounded px-3 py-2">
        </div>
      </div>

      <div>
        <label class="block text-gray-300 mb-1">Position</label>
        <input name="position" type="number" value="{{ old('position',$banner->position) }}"
               class="w-32 bg-gray-800 text-white rounded px-3 py-2">
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <a href="{{ route('admin.banners.index') }}" class="px-4 py-2 bg-gray-700 rounded text-white">Cancel</a>
        <button class="px-5 py-2 bg-green-600 hover:bg-green-700 rounded text-white">Save</button>
      </div>
    </form>
  </div>

  <script>
    const typeSel = document.getElementById('cta_type');
    const idWrap  = document.getElementById('cta_id_wrap');
    const urlWrap = document.getElementById('cta_url_wrap');
    function updateCtaVisibility(){
      const v = typeSel.value;
      idWrap.style.display  = (v === 'song' || v === 'playlist' || v === 'album') ? 'block' : 'none';
      urlWrap.style.display = (v === 'url') ? 'block' : 'none';
    }
    typeSel.addEventListener('change', updateCtaVisibility);
    updateCtaVisibility();
  </script>
</x-app-layout>
