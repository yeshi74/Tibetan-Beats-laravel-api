@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Artist Applications</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Avatar</th>
                <th>Display Name</th>
                <th>Status</th>
                <th>Applied At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($artists as $artist)
                <tr>
                    <td>{{ $artist->id }}</td>
                    <td>
                        @if($artist->user)
                            {{ $artist->user->name }} <br>
                            <small class="text-muted">{{ $artist->user->email }}</small>
                        @else
                            <span class="text-muted">[deleted user]</span>
                        @endif
                    </td>
                    <td>
                        @if($artist->profile_image)
                            <img
                                src="{{ asset('storage/'.$artist->profile_image) }}"
                                width="60"
                                height="60"
                                style="object-fit:cover;border-radius:50%;">
                        @else
                            -
                        @endif
                        </td>
                    <td>{{ $artist->display_name }}</td>
                    
                    <td>
                        @if($artist->verified)
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>{{ $artist->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.artists.show', $artist->id) }}"
   class="btn btn-primary btn-sm mb-1">
    Review
</a>

@if(!$artist->verified)
    <form method="POST"
          action="{{ route('admin.artists.approve', $artist->id) }}"
          class="d-inline">
        @csrf
        @method('PATCH')

        <button type="submit"
                class="btn btn-success btn-sm">
            Approve
        </button>
    </form>

    <form method="POST"
          action="{{ route('admin.artists.reject', $artist->id) }}"
          class="d-inline">
        @csrf
        @method('PATCH')

        <button type="submit"
                class="btn btn-danger btn-sm">
            Reject
        </button>
    </form>
                        @else
                            <form method="POST" action="{{ route('admin.artists.reject', $artist->id) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-secondary">Revoke</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No artist applications yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3">
        {!! $artists->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
