<div class="bg-gray-800 p-6 rounded-lg">

    <h2 class="text-2xl font-bold text-amber-400 mb-6">
        ⚖ Ownership Claim #{{ $claim->id }}
    </h2>

    <div class="space-y-4">

        <div>
            <strong>Song Title:</strong><br>
            {{ $claim->song_title }}
        </div>

        <div>
            <strong>Claimant:</strong><br>
            {{ $claim->claimant_name }}
        </div>

        <div>
            <strong>Email:</strong><br>
            {{ $claim->email }}
        </div>

        <div>
            <strong>Status:</strong><br>
            {{ ucfirst($claim->status) }}
        </div>

        <div>
            <strong>Reason:</strong><br>
            {{ $claim->reason }}
        </div>

        @if($claim->admin_notes)
            <div>
                <strong>Admin Notes:</strong><br>
                {{ $claim->admin_notes }}
            </div>
        @endif

    </div>

    @if($claim->status === 'pending')

    <div class="mt-8 flex gap-3">

        <form method="POST"
              action="{{ route('admin.song-claims.approve', $claim) }}">
            @csrf
            @method('PATCH')

            <button
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Approve & Transfer Ownership
            </button>
        </form>

        <form method="POST"
              action="{{ route('admin.song-claims.reject', $claim) }}">
            @csrf
            @method('PATCH')

            <input
                type="text"
                name="admin_notes"
                placeholder="Reason for rejection"
                class="px-3 py-2 rounded bg-gray-700 text-white">

            <button
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Reject Claim
            </button>
        </form>

    </div>

    @endif

</div>