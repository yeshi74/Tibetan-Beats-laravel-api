<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <a href="{{ route('admin.in_app_messages.index') }}" class="text-sm text-gray-400 mb-4 inline-block">← back</a>

    <h1 class="text-2xl font-bold text-gray-100 mb-4">Edit In-App Message</h1>

    <form method="post" action="{{ route('admin.in_app_messages.update', $m) }}" enctype="multipart/form-data">
      @csrf
      @method('PATCH')

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Key (optional)</label>
        <input name="key" value="{{ old('key', $m->key) }}" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="slug_key" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <select name="type" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="modal" {{ old('type', $m->type) === 'modal' ? 'selected' : '' }}>Modal</option>
          <option value="banner" {{ old('type', $m->type) === 'banner' ? 'selected' : '' }}>Banner</option>
          <option value="card" {{ old('type', $m->type) === 'card' ? 'selected' : '' }}>Card</option>
          <option value="bottom_sheet" {{ old('type', $m->type) === 'bottom_sheet' ? 'selected' : '' }}>Bottom sheet</option>
          <option value="interstitial" {{ old('type', $m->type) === 'interstitial' ? 'selected' : '' }}>Interstitial</option>
        </select>

        <input name="priority" type="number" value="{{ old('priority', $m->priority) }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="Priority" />

        <input name="audience" value="{{ old('audience', $m->audience) }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" placeholder="audience (all or role:moderator or users:[1,2])" />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Title (en)</label>
        <input name="title_en" required value="{{ old('title_en', $m->title['en'] ?? '') }}" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded" />
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Body (en)</label>
        <textarea name="body_en" rows="4" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded">{{ old('body_en', $m->body['en'] ?? '') }}</textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Image (optional)</label>
        @if(!empty($m->image_url))
          <div class="mb-2">
            <img src="{{ $m->image_url }}" alt="current" class="w-48 h-auto object-cover rounded border" />
          </div>
        @endif
        <input type="file" name="image" class="block" />
        <div class="text-xs text-gray-400 mt-1">Or paste an image URL below</div>
        <input type="text" name="image_url" value="{{ old('image_url', $m->image_url) }}" class="w-full bg-gray-50 text-gray-800 px-3 py-2 rounded mt-1" />
      </div>

      <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <select name="cta_type" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="" {{ empty($m->cta) ? 'selected' : '' }}>No CTA</option>
          <option value="route" {{ optional($m->cta)['type'] === 'route' ? 'selected' : '' }}>In-app route</option>
          <option value="url" {{ optional($m->cta)['type'] === 'url' ? 'selected' : '' }}>External URL</option>
        </select>
        <input name="cta_value" placeholder="/route or https://..." value="{{ old('cta_value', optional($m->cta)['value'] ?? '') }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <input name="cta_label_en" placeholder="CTA label (en)" value="{{ old('cta_label_en', optional($m->cta)['label']['en'] ?? '') }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
      </div>

      <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($m->start_at)->format('Y-m-d\TH:i')) }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($m->end_at)->format('Y-m-d\TH:i')) }}" class="bg-gray-50 text-gray-800 px-3 py-2 rounded" />
        <select name="status" class="bg-gray-50 text-gray-800 px-3 py-2 rounded">
          <option value="active" {{ old('status', $m->status) === 'active' ? 'selected' : '' }}>Active</option>
          <option value="draft" {{ old('status', $m->status) === 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="archived" {{ old('status', $m->status) === 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
      </div>

      <div class="mb-4 flex gap-3">
        <label class="flex items-center gap-2">
          <input type="checkbox" name="dismissible" {{ old('dismissible', $m->dismissible) ? 'checked' : '' }} />
          <span class="text-sm text-gray-300">Dismissible</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="checkbox" name="dismiss_persists" {{ old('dismiss_persists', $m->dismiss_persists) ? 'checked' : '' }} />
          <span class="text-sm text-gray-300">Persist dismissal (hide forever)</span>
        </label>
      </div>

      <div class="flex gap-3">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save changes</button>
        <a href="{{ route('admin.in_app_messages.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded">Cancel</a>
      </div>
    </form>
  </div>
</x-app-layout>
