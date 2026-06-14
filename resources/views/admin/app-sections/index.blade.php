@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Home Sections</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.app-sections.create') }}"
       class="btn btn-primary mb-3">
        + Add Section
    </a>

    <table class="table table-bordered table-hover">

        <thead class="table-dark">
            <tr>
                <th>Order</th>
                <th>Page</th>
                <th>Title</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Limit</th>
                <th>Preview</th>
                <th>Active</th>
                <th width="150">Actions</th>
            </tr>
        </thead>

        <tbody>

        @forelse($sections as $section)

            <tr>
                <td>{{ $section->sort_order }}</td>

                <td>{{ ucfirst($section->page) }}</td>

                <td>{{ $section->title }}</td>

                <td>{{ $section->section_type }}</td>

                <td>
                    @if($section->section_type === 'genre')
                        {{ $section->genre?->name ?? '-' }}

                    @elseif($section->section_type === 'playlist')
                        {{ $section->playlist?->name ?? '-' }}

                    @else
                        —
                    @endif
                </td>

                <td>{{ $section->limit }}</td>

                @php
                    $preview = match($section->section_type) {

                        'popular_artists' =>
                            "Top {$section->limit} verified artists",

                        'latest_songs' =>
                            "Latest {$section->limit} songs",

                        'trending_songs' =>
                            "Top {$section->limit} by plays",

                        'most_liked_songs' =>
                            "Top {$section->limit} liked songs",

                        'genre' =>
                            "Genre: " . optional($section->genre)->name,

                        'playlist' =>
                            "Playlist: " . optional($section->playlist)->name,

                        'verified_artists' =>
                            "Verified artists",

                        'featured_genres' =>
                            "Featured genres",

                        'featured_playlists' =>
                            "Featured playlists",

                        default => '-',
                    };
                    @endphp

                    <td>{{ $preview }}</td>

                <td>
                    @if($section->is_active)
                        <span class="badge bg-success">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('admin.app-sections.edit', $section) }}"
                       class="btn btn-sm btn-warning">
                        Edit
                    </a>

                    <form
                        action="{{ route('admin.app-sections.destroy', $section) }}"
                        method="POST"
                        style="display:inline;"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            onclick="return confirm('Delete this section?')"
                            class="btn btn-sm btn-danger"
                        >
                            Delete
                        </button>
                    </form>
                </td>
            </tr>

        @empty

            <tr>
                <td colspan="6" class="text-center">
                    No sections found
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

    {{ $sections->links('pagination::bootstrap-5') }}

</div>
@endsection