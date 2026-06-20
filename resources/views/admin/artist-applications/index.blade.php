@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Artist Applications</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Display Name</th>
                <th>Country</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
        @forelse($applications as $application)
            <tr>
                <td>{{ $application->id }}</td>

                <td>
                    {{ $application->user?->name }}
                    <br>
                    <small>{{ $application->user?->email }}</small>
                </td>

                <td>
                    {{ $application->display_name }}
                </td>

                <td>
                    {{ $application->country ?: '-' }}
                </td>

                <td>
                    <span class="
                        @if($application->status === 'approved')
                            text-success
                        @elseif($application->status === 'rejected')
                            text-danger
                        @else
                            text-warning
                        @endif
                    ">
                        {{ ucfirst($application->status) }}
                    </span>
                </td>

                <td>
                    {{ $application->created_at->format('d M Y') }}
                </td>

                <td>
                    <a href="{{ route('admin.artist-applications.show', $application) }}"
                       class="btn btn-primary btn-sm">
                        Review
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">
                    No artist applications yet.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $applications->links() }}

</div>
@endsection