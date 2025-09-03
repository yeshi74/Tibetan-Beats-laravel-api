@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Playlists</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.playlists.create') }}" class="btn btn-primary">+ Create Playlist</a>
        <form method="GET" action="{{ route('admin.playlists.index') }}" class="d-flex">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search playlists..."
                   class="form-control me-2">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Cover</th>
                <th>Name</th>
                <th>Songs</th>
                <th>Created By</th>
                <th>Featured</th>
                <th>Created At</th>
                <th width="180">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($playlists as $playlist)
                <tr>
                    <td>{{ $playlist->id }}</td>

                    <!-- Cover image -->
                    <td>
                        @if($playlist->cover_image)
                            <img src="{{ asset('storage/' . $playlist->cover_image) }}" 
                                 alt="Cover" class="img-thumbnail" width="60" height="60">
                        @else
                            <span class="text-muted">No Cover</span>
                        @endif
                    </td>

                    <!-- Name -->
                    <td>{{ $playlist->name }}</td>

                    <!-- Songs count -->
                    <td>{{ $playlist->songs_count }}</td>

                    <!-- Created by -->
                    <td>
                        @if($playlist->user_id)
                            {{ $playlist->user->name ?? 'User #'.$playlist->user_id }}
                        @else
                            <span class="badge bg-primary">Admin</span>
                        @endif
                    </td>

                    <!-- Featured -->
                    <td>
                        <form action="{{ route('admin.playlists.toggleFeatured', $playlist) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            @if($playlist->is_featured)
                                <button type="submit" class="btn btn-sm btn-success">
                                    Yes
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    No
                                </button>
                            @endif
                        </form>
                    </td>


                    <!-- Created date -->
                    <td>{{ $playlist->created_at->format('d M Y') }}</td>

                    <!-- Actions -->
                    <td>
                        <a href="{{ route('admin.playlists.edit', $playlist) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.playlists.destroy', $playlist) }}" 
                              method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Delete this playlist?')" 
                                    class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No playlists found</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ✅ Pagination -->
    <div class="d-flex justify-content-center mt-3">
        {!! $playlists->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
