<x-app-layout>
<div class="max-w-6xl mx-auto p-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">
            Comments for Dedication #{{ $dedication->id }}
        </h1>

        <a href="{{ route('admin.moderation.dedications.index') }}"
           class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
            Back
        </a>
    </div>

    <div class="mb-4 text-gray-300">
        Total Comments:
        <span class="font-bold text-white">
            {{ $comments->total() }}
        </span>
    </div>

    @forelse($comments as $comment)

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-4">

            <div class="flex justify-between items-start">

                <div>
                    <div class="font-semibold text-white">
                        {{ $comment->user->name ?? 'Unknown User' }}
                    </div>

                    <div class="text-xs text-gray-400 mt-1">
                        #{{ $comment->id }}
                        •
                        {{ $comment->created_at->format('d M Y H:i') }}
                    </div>
                </div>

                <form method="POST"
                    action="{{ route('admin.moderation.dedication-comments.destroy', $comment) }}"
                    onsubmit="return confirm('Delete this comment?')">

                    @csrf
                    @method('DELETE')

                    <button
                        class="px-3 py-1 text-sm rounded bg-red-600 text-white hover:bg-red-500">
                        Delete
                    </button>

                </form>

            </div>

            <div class="mt-3 text-gray-200">
                {{ $comment->comment }}
            </div>

        </div>

    @empty

        <div class="bg-yellow-100 text-yellow-900 p-4 rounded">
            No comments found.
        </div>

    @endforelse

    <div class="mt-6">
        {{ $comments->links() }}
    </div>

</div>
</x-app-layout>