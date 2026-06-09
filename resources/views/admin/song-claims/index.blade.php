@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <h2 class="mb-4">⚖ Ownership Claims</h2>

    <div class="card bg-dark text-light">
        <div class="card-body">

            @if($claims->count())

                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Song</th>
                            <th>Claimant</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($claims as $claim)
                        <tr>
                            <td>#{{ $claim->id }}</td>

                            <td>
                                {{ $claim->song_title }}
                            </td>

                            <td>
                                {{ $claim->claimant_name }}
                            </td>

                            <td>
                                @if($claim->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                                @elseif($claim->status === 'approved')
                                    <span class="badge bg-success">
                                        Approved
                                    </span>
                                @elseif($claim->status === 'rejected')
                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($claim->status) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $claim->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <a href="{{ route('admin.song-claims.show', $claim) }}">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>

                {{ $claims->links() }}

            @else

                <div class="alert alert-info">
                    No ownership claims found.
                </div>

            @endif

        </div>
    </div>

</div>
@endsection