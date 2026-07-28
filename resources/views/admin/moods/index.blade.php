@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2 class="mb-4">Listen by Mood</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('admin.moods.create') }}" class="btn btn-primary mb-3">
            + Add Mood
        </a>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Emoji</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Songs</th>
                    <th>Created</th>
                    <th width="170">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($moods as $mood)
                    <tr>
                        <td>{{ $mood->id }}</td>
                        <td style="font-size:22px;">{{ $mood->emoji }}</td>
                        <td>{{ $mood->name }}</td>
                        <td>{{ $mood->description ?: '-' }}</td>
                        <td>{{ $mood->sort_order }}</td>

                        <td>
                            @if ($mood->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>{{ $mood->songs_count }}</td>

                        <td>{{ $mood->created_at->format('d M Y') }}</td>

                        <td>
                            <a href="{{ route('admin.moods.edit', $mood) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('admin.moods.destroy', $mood) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this mood?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            No moods found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {!! $moods->links('pagination::bootstrap-5') !!}
        </div>
    </div>
@endsection
