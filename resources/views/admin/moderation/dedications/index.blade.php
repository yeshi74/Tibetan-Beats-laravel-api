<x-app-layout>
  <div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-100 mb-6">Dedications Moderation</h1>

    {{-- status filter --}}
    <form method="get" class="mb-5">
      <label class="block text-sm text-gray-300 mb-2">Filter by status</label>
      <select name="status"
              class="w-full sm:w-80 bg-gray-800 text-gray-100 border border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              onchange="this.form.submit()">
        <option value="" {{ request('status')==='' ? 'selected' : '' }}>All</option>
        <option value="pending"  {{ request('status')==='pending'  ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected</option>
      </select>
    </form>

    {{-- bulk actions --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">

      <a href="{{ route('admin.moderation.dedications.create') }}"
        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
          Create Dedication
      </a>
      <form id="bulk-approve" method="post" action="{{ route('admin.moderation.dedications.bulkApprove') }}">
        @csrf @method('PATCH')
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400">
          Approve Selected
        </button>
      </form>

      <form id="bulk-reject" method="post" action="{{ route('admin.moderation.dedications.bulkReject') }}">
        @csrf @method('PATCH')
        <input type="hidden" name="reason" id="bulk-reject-reason" value="Bulk reject">
        <button type="button"
                onclick="promptBulkReject()"
                class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-400">
          Reject Selected
        </button>
      </form>
    </div>

    {{-- table --}}
    <div class="overflow-x-auto rounded-xl border border-gray-700 shadow">
      <div class="overflow-x-auto rounded-xl border border-gray-300 shadow">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-900 text-gray-100 uppercase tracking-wide text-xs">
      <tr>
        <th class="px-4 py-3 w-10">
          <input id="chk-all" type="checkbox"
                 class="h-4 w-4 align-middle"
                 onclick="toggleAll(this)">
        </th>
        <th class="px-4 py-3">ID</th>
        <th class="px-4 py-3">User</th>
        <th class="px-4 py-3">Song</th>
        <th class="px-4 py-3">Text</th>
        <th class="px-4 py-3">Comments</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3">Reviewed By</th>
        <th class="px-4 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 bg-white text-gray-800">
      @foreach($dedications as $d)
        <tr class="hover:bg-gray-100">
          <td class="px-4 py-3">
            <input class="chk h-4 w-4 align-middle" type="checkbox" value="{{ $d->id }}">
          </td>
          <td class="px-4 py-3 font-medium">{{ $d->id }}</td>
          <td class="px-4 py-3">{{ optional($d->user)->name ?? '—' }}</td>
          <td class="px-4 py-3">{{ optional($d->song)->title ?? '—' }}</td>
          <td class="px-4 py-3">{{ Str::limit($d->text, 80) }}</td>
          <td class="px-4 py-3">
            <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold">
                {{ $d->comments_count }}
            </span>
        </td>
          <td class="px-4 py-3">
            <span @class([
                'px-2 py-1 rounded-full text-xs font-semibold',
                'bg-green-100 text-green-800' => $d->status === 'approved',
                'bg-red-100 text-red-800'     => $d->status === 'rejected',
                'bg-yellow-100 text-yellow-800' => $d->status === 'pending',
            ])>
              {{ ucfirst($d->status) }}
            </span>
          </td>
          <td class="px-4 py-3">{{ optional($d->reviewer)->name ?? '—' }}</td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-2">

              <a href="{{ route('admin.moderation.dedications.show', $d) }}"
                class="px-3 py-1 text-sm rounded bg-sky-600 text-white hover:bg-sky-500">
                  View
              </a>

              <a href="{{ route('admin.moderation.dedications.comments', $d) }}"
                class="px-3 py-1 text-sm rounded bg-blue-600 text-white hover:bg-blue-500">
                  Comments
              </a>
            
            <form method="post" action="{{ route('admin.moderation.dedications.approve',$d) }}">
              @csrf @method('PATCH')
              <button class="px-3 py-1 text-sm rounded bg-green-600 text-white hover:bg-green-500">Approve</button>
            </form>
            <form method="post" action="{{ route('admin.moderation.dedications.reject',$d) }}" onsubmit="return confirmReject(this);">
              @csrf @method('PATCH')
              <input type="hidden" name="reason" value="">
              <button class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-500">Reject</button>
            </form>

            <form method="POST"
              action="{{ route('admin.moderation.dedications.destroy', $d) }}" onsubmit="return confirm('Delete this dedication permanently?')">

            @csrf
            @method('DELETE')

            <button
                type="submit" class="px-3 py-1 text-sm rounded bg-gray-800 text-white hover:bg-black">
                Delete
            </button>
        </form>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

    </div>

    <div class="mt-5">
      {{ $dedications->withQueryString()->links() }}
    </div>
  </div>

  {{-- helpers --}}
  <script>
    function toggleAll(master) {
      document.querySelectorAll('.chk').forEach(c => c.checked = master.checked);
    }
    function injectIds(form) {
      form.querySelectorAll('input[name="ids[]"]').forEach(n => n.remove());
      document.querySelectorAll('.chk:checked').forEach(c => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'ids[]'; input.value = c.value;
        form.appendChild(input);
      });
    }
    function promptBulkReject() {
      const form = document.getElementById('bulk-reject');
      const reason = prompt('Reject reason (optional):', 'Bulk reject');
      if (reason !== null) {
        document.getElementById('bulk-reject-reason').value = reason;
        injectIds(form); form.submit();
      }
    }
    function confirmReject(form) {
      const reason = prompt('Reject reason (optional):', '');
      if (reason === null) return false;
      form.querySelector('input[name="reason"]').value = reason;
      return true;
    }
    document.getElementById('bulk-approve')?.addEventListener('submit', function () {
      injectIds(this);
    });
  </script>
</x-app-layout>
