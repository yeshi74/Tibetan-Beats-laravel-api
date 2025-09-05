<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-4">Create In-App Message</h1>

    <form method="post" action="{{ route('admin.in_app_messages.store') }}" enctype="multipart/form-data">
      @csrf

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Key (optional)</label>
        <input name="key" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="slug_key" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <select name="type" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="modal">Modal</option>
          <option value="banner">Banner</option>
          <option value="card">Card</option>
          <option value="bottom_sheet">Bottom sheet</option>
          <option value="interstitial">Interstitial</option>
        </select>

        <input name="priority" type="number" value="50" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="Priority" />

        <input name="audience" value="all" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="audience (all or role:moderator or users:[1,2])" />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Title (en)</label>
        <input name="title_en" required class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded" />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Body (en)</label>
        <textarea name="body_en" rows="4" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded"></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Image (optional)</label>
        <input type="file" name="image" class="block" />
        <div class="text-xs text-gray-400 mt-1">Or paste an image URL below</div>
        <input type="text" name="image_url" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded mt-1" />
      </div>

      <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <select name="cta_type" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="">No CTA</option>
          <option value="route">In-app route</option>
          <option value="url">External URL</option>
        </select>
        <input name="cta_value" placeholder="/route or https://..." class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <input name="cta_label_en" placeholder="CTA label (en)" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
      </div>

      <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="datetime-local" name="start_at" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <input type="datetime-local" name="end_at" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <select name="status" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="active">Active</option>
          <option value="draft">Draft</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      <div class="mb-4 flex gap-3">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="dismissible" checked />
          <span class="text-sm text-gray-300">Dismissible</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="dismiss_persists" checked />
          <span class="text-sm text-gray-300">Persist dismissal (hide forever)</span>
        </label>
      </div>

      <div class="flex gap-3">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
        <a href="{{ route('admin.in_app_messages.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded">Cancel</a>
      </div>
    </form>
  </div>
</x-app-layout>
