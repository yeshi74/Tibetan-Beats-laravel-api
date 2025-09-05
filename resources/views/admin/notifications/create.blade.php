<x-app-layout>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-4">Create Notification</h1>

    <form method="post" action="{{ route('admin.notifications.store') }}">
      @csrf

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Title</label>
        <input name="title" required class="w-full bg-gray-800 text-white rounded px-3 py-2">
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Body</label>
        <textarea name="body" rows="4" class="w-full bg-gray-800 text-white rounded px-3 py-2"></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Audience</label>
        <select name="audience" class="w-full bg-gray-800 text-white rounded px-3 py-2">
          <option value="all">All users</option>
          <option value="premium">Premium users</option>
          <option value="role:admin">Admins</option>
          <option value="role:moderator">Moderators</option>
          <option value='users:[1]'>Specific users (use JSON users:[1,2,3])</option>
        </select>
        <div class="text-xs text-gray-400 mt-1">For specific users, use format <code>users:[1,2,3]</code>.</div>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Payload (optional JSON)</label>
        <textarea name="payload" rows="3" class="w-full bg-gray-800 text-white rounded px-3 py-2" placeholder='{"key":"value"}'></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm text-gray-300">Scheduled at (optional)</label>
        <input type="datetime-local" name="scheduled_at" class="w-full bg-gray-800 text-white rounded px-3 py-2" />
      </div>

      <div class="flex gap-3">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Send / Queue</button>
        <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-gray-700 text-white rounded">Cancel</a>
      </div>
    </form>
  </div>
</x-app-layout>
