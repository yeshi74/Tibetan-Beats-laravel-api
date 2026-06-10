<x-app-layout>

<div class="max-w-7xl mx-auto py-8 px-6">

    <h2 class="text-2xl font-bold mb-6">
        🚩 Song Reports
    </h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-gray-900 rounded-lg">

        <table class="w-full text-sm text-left text-gray-300">

            <thead class="bg-gray-800">
                <tr>
                    <th class="px-4 py-3">Song</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Reported At</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>

            <tbody>

            @forelse($reports as $report)

                <tr class="border-b border-gray-700">

                    <td class="px-4 py-3">
                        {{ $report->song?->title ?? 'Deleted Song' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $report->user?->name ?? 'Unknown User' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $report->reason }}
                    </td>

                    <td class="px-4 py-3">
                        {{ ucfirst($report->status) }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $report->created_at->diffForHumans() }}
                    </td>

                    <td class="px-4 py-3">

                        @if($report->status === 'pending')

                            <form
                                action="{{ route('admin.song-reports.review',$report) }}"
                                method="POST"
                                class="inline"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    class="bg-green-600 text-white px-3 py-1 rounded"
                                >
                                    Review
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.song-reports.dismiss',$report) }}"
                                method="POST"
                                class="inline"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    class="bg-red-600 text-white px-3 py-1 rounded"
                                >
                                    Dismiss
                                </button>
                            </form>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center py-6">
                        No reports found
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $reports->links() }}
    </div>

</div>

</x-app-layout>