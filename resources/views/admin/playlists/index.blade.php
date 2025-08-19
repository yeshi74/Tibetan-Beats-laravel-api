@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Playlists</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.playlists.create') }}" class="btn btn-primary mb-3">+ Create Playlist</a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Songs</th>
                <th>Created</th>
                <th width="150">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($playlists as $playlist)
                <tr>
                    <td>{{ $playlist->id }}</td>
                    <td>{{ $playlist->name }}</td>
                    <td>{{ $playlist->songs_count }}</td>
                    <td>{{ $playlist->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.playlists.edit', $playlist) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.playlists.destroy', $playlist) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this playlist?')" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No playlists found</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $playlists->links() }}
</div>
@endsection
