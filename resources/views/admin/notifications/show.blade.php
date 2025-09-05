<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <a href="{{ route('admin.notifications.index') }}" class="text-sm text-gray-400 mb-4 inline-block">← back</a>
    <div class="bg-white text-gray-900 p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-2">{{ $n->title }}</h1>
      <div class="text-sm text-gray-600 mb-4">Audience: {{ $n->audience }} • Sent: {{ $n->sent_count }} • Type: {{ $n->type ?? '—' }}</div>
      <div class="mb-4">{!! nl2br(e($n->body)) !!}</div>
      <div class="text-xs text-gray-500">Created by: {{ optional($n->creator)->name ?? 'system' }} • {{ optional($n->created_at)->toDayDateTimeString() }}</div>
      <pre class="mt-4 p-3 bg-gray-100 rounded text-sm">{{ json_encode($n->payload ?? [], JSON_PRETTY_PRINT) }}</pre>
    </div>
  </div>
</x-app-layout>
