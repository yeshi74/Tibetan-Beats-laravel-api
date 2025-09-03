@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Genres</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.genres.create') }}" class="btn btn-primary mb-3">+ Add Genre</a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Featured</th>
                <th>Created</th>
                <th width="150">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($genres as $genre)
                <tr>
                    <td>{{ $genre->id }}</td>
                    <td>{{ $genre->name }}</td>
                    <td>{{ $genre->description }}</td>
                    <td>
                        @if($genre->is_featured)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td>{{ $genre->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this genre?')" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No genres found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-3">
        {!! $genres->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
