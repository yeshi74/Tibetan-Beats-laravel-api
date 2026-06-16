<x-app-layout>

<div class="max-w-5xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">
            Dedication #{{ $d->id }}
        </h1>

        <a href="{{ route('admin.moderation.dedications.index') }}"
           class="px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-600">
            Back
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg p-6 space-y-6">

        <div class="flex flex-wrap gap-2 mb-6">

    <a href="{{ route('admin.moderation.dedications.comments', $d) }}"
       class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-500">
        View Comments
    </a>

    @if($d->status !== 'approved')
        <form method="POST"
              action="{{ route('admin.moderation.dedications.approve', $d) }}">
            @csrf
            @method('PATCH')

            <button
                class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-500">
                Approve
            </button>
        </form>
    @endif

    @if($d->status !== 'rejected')
        <form method="POST"
              action="{{ route('admin.moderation.dedications.reject', $d) }}"
              onsubmit="return confirmReject(this)">
            @csrf
            @method('PATCH')

            <input type="hidden" name="reason" value="">

            <button
                class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500">
                Reject
            </button>
        </form>
    @endif

    <form method="POST"
          action="{{ route('admin.moderation.dedications.destroy', $d) }}"
          onsubmit="return confirm('Delete this dedication permanently?')">

        @csrf
        @method('DELETE')

        <button
            class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-black">
            Delete
        </button>
    </form>

</div>

        {{-- Status --}}
        <div>
            <span @class([
                'px-3 py-1 rounded-full text-sm font-semibold',
                'bg-green-100 text-green-800' => $d->status === 'approved',
                'bg-red-100 text-red-800' => $d->status === 'rejected',
                'bg-yellow-100 text-yellow-800' => $d->status === 'pending',
            ])>
                {{ ucfirst($d->status) }}
            </span>
        </div>

        {{-- Sender --}}
        <div>
            <div class="text-gray-400 text-sm">Sender</div>
            <div class="text-white font-semibold">
                {{ $d->user->name ?? 'Unknown User' }}
            </div>
        </div>

        {{-- Recipient --}}
        <div>
            <div class="text-gray-400 text-sm">Recipient</div>
            <div class="text-white">
                {{ $d->to_name }}
            </div>

            @if($d->toUser)
                <div class="text-xs text-gray-500 mt-1">
                    Linked User: {{ $d->toUser->name }}
                </div>
            @endif
        </div>

        {{-- Song --}}
        <div>
            <div class="text-gray-400 text-sm">Song</div>
            <div class="text-white">
                {{ $d->song->title ?? 'Unknown Song' }}
            </div>
        </div>

        {{-- Message --}}
        <div>
            <div class="text-gray-400 text-sm mb-2">
                Message
            </div>

            <div class="bg-gray-900 rounded p-4 text-white whitespace-pre-wrap">
                {{ $d->message }}
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-gray-900 p-4 rounded">
                <div class="text-gray-400 text-xs">Comments</div>
                <div class="text-white text-xl font-bold">
                    {{ $d->comments_count }}
                </div>
            </div>

            <div class="bg-gray-900 p-4 rounded">
                <div class="text-gray-400 text-xs">Likes</div>
                <div class="text-white text-xl font-bold">
                    {{ $d->likes_count }}
                </div>
            </div>

            <div class="bg-gray-900 p-4 rounded">
                <div class="text-gray-400 text-xs">Created</div>
                <div class="text-white text-sm">
                    {{ $d->created_at?->format('d M Y') }}
                </div>
            </div>

            <div class="bg-gray-900 p-4 rounded">
                <div class="text-gray-400 text-xs">Reviewed By</div>
                <div class="text-white text-sm">
                    {{ $d->reviewer->name ?? '—' }}
                </div>
            </div>

        </div>

    </div>

</div>

<script>
function confirmReject(form)
{
    const reason = prompt(
        'Reject reason (optional):',
        ''
    );

    if (reason === null) {
        return false;
    }

    form.querySelector(
        'input[name="reason"]'
    ).value = reason;

    return true;
}
</script>

</x-app-layout>