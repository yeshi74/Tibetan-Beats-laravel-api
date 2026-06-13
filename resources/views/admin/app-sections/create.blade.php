@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Add Section</h2>

    <form action="{{ route('admin.app-sections.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Page</label>

            <select name="page" class="form-control" required>
                <option value="home">Home</option>
                <option value="search">Search</option>
                <option value="library">Library</option>
                <option value="dedications">Dedications</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>

            <input
                type="text"
                name="title"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Section Type</label>

            <select
                name="section_type"
                id="section_type"
                class="form-control"
                required
            >
                <option value="popular_artists">Popular Artists</option>
                <option value="latest_songs">Latest Songs</option>
                <option value="trending_songs">Trending Songs</option>
                <option value="most_liked_songs">Most Liked Songs</option>
                <option value="genre">Genre</option>
                <option value="playlist">Playlist</option>
                <option value="verified_artists">Verified Artists</option>
                <option value="featured_genres">Featured Genres</option>
                <option value="featured_playlists">Featured Playlists</option>
            </select>
        </div>

        <div
            class="mb-3">
            <label class="form-label">Genre</label>

            <select
                name="genre_id"
                class="form-control"
            >
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}">
                        {{ $genre->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div
            class="mb-3">
            <label class="form-label">Playlist</label>

            <select
                name="playlist_id"
                class="form-control"
            >
                @foreach($playlists as $playlist)
                    <option value="{{ $playlist->id }}">
                        {{ $playlist->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Sort Order</label>

            <input
                type="number"
                name="sort_order"
                value="1"
                class="form-control"
                required
            >
        </div>

        <div class="form-check mb-3">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                checked
                class="form-check-input"
                id="active"
            >

            <label
                for="active"
                class="form-check-label"
            >
                Active
            </label>
        </div>

        <button
            type="submit"
            class="btn btn-success"
        >
            Save
        </button>

        <a
            href="{{ route('admin.app-sections.index') }}"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </form>

</div>
@endsection

