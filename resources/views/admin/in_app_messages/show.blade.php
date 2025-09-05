<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <a href="{{ route('admin.in_app_messages.index') }}" class="text-sm text-gray-400 mb-4 inline-block">← back</a>

    <div class="bg-white text-gray-900 p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-2">{{ $m->title['en'] ?? '—' }}</h1>
      <div class="text-sm text-gray-600 mb-4">{{ $m->audience }} • Priority {{ $m->priority }}</div>

      @if($m->image_url)
        <img src="{{ $m->image_url }}" alt="" class="w-full max-h-48 object-cover rounded mb-4" />
      @endif

      <div class="mb-4">{!! nl2br(e($m->body['en'] ?? '')) !!}</div>

      <div class="text-xs text-gray-500">Status: {{ $m->status }} • Created: {{ optional($m->created_at)->toDayDateTimeString() }}</div>

      <pre class="mt-4 p-3 bg-gray-100 rounded text-sm">{{ json_encode($m->cta ?? [], JSON_PRETTY_PRINT) }}</pre>
    </div>
  </div>
</x-app-layout>
