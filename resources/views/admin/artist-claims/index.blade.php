@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Artist Claims</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Artist</th>
                <th>Claimant</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
        @forelse($claims as $claim)
            <tr>
                <td>{{ $claim->id }}</td>

                <td>
                    {{ $claim->artist?->display_name }}
                </td>

                <td>
                    {{ $claim->claimant_name }}
                    <br>
                    <small>{{ $claim->email }}</small>
                </td>

                <td>
                    {{ ucfirst($claim->status) }}
                </td>

                <td>
                    {{ $claim->created_at->format('d M Y') }}
                </td>

                <td>
                    <a href="{{ route('admin.artist-claims.show', $claim) }}"
                       class="btn btn-primary btn-sm">
                        Review
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">
                    No artist claims yet.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $claims->links() }}

</div>
@endsection