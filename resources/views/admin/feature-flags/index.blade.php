<x-app-layout>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-6">Feature Flags</h1>

    {{-- create new --}}
    <form method="post" action="{{ route('admin.feature_flags.store') }}" class="mb-6 flex gap-3">
      @csrf
      <input type="text" name="key" placeholder="key (e.g. dedications)" class="px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
      <input type="text" name="name" placeholder="Feature name" class="px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
      <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">Add</button>
    </form>

    <table class="w-full text-sm bg-gray-900 border border-gray-700 rounded">
      <thead class="bg-gray-800 text-gray-300 uppercase text-xs">
        <tr>
          <th class="px-4 py-2 text-left">Key</th>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2">Enabled</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800 text-gray-200">
        @foreach($flags as $flag)
          <tr>
            <td class="px-4 py-2">{{ $flag->key }}</td>
            <td class="px-4 py-2">{{ $flag->name }}</td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 rounded-full text-xs font-semibold
                {{ $flag->enabled ? 'bg-emerald-700 text-emerald-100' : 'bg-rose-700 text-rose-100' }}">
                {{ $flag->enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </td>
            <td class="px-4 py-2">
              <form method="post" action="{{ route('admin.feature_flags.toggle',$flag) }}">
                @csrf @method('PATCH')
                <button class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600">
                  Toggle
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-app-layout>
