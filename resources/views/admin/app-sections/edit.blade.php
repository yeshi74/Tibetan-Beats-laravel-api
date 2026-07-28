@extends('layouts.admin')

@section('content')
    <div class="container">

        <h2 class="mb-4">Edit Section</h2>

        <form action="{{ route('admin.app-sections.update', $appSection) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Page</label>

                <select name="page" class="form-control" required>
                    <option value="home" {{ $appSection->page == 'home' ? 'selected' : '' }}>
                        Home
                    </option>

                    <option value="search" {{ $appSection->page == 'search' ? 'selected' : '' }}>
                        Search
                    </option>

                    <option value="library" {{ $appSection->page == 'library' ? 'selected' : '' }}>
                        Library
                    </option>

                    <option value="dedications" {{ $appSection->page == 'dedications' ? 'selected' : '' }}>
                        Dedications
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Title</label>

                <input type="text" name="title" value="{{ old('title', $appSection->title) }}" class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Section Type</label>

                <select name="section_type" id="section_type" class="form-control" required
                    onchange="
                    document.getElementById('genre-wrapper').style.display =
                        this.value === 'genre' ? 'block' : 'none';

                    document.getElementById('playlist-wrapper').style.display =
                        this.value === 'playlist' ? 'block' : 'none';
                ">
                    <optgroup label="Home">
                        <option value="popular_artists"
                            {{ $appSection->section_type == 'popular_artists' ? 'selected' : '' }}>
                            Popular Artists
                        </option>

                        <option value="latest_songs" {{ $appSection->section_type == 'latest_songs' ? 'selected' : '' }}>
                            Latest Songs
                        </option>

                        <option value="trending_songs"
                            {{ $appSection->section_type == 'trending_songs' ? 'selected' : '' }}>
                            Trending Songs
                        </option>

                        <option value="most_liked_songs"
                            {{ $appSection->section_type == 'most_liked_songs' ? 'selected' : '' }}>
                            Most Liked Songs
                        </option>

                        <option value="genre" {{ $appSection->section_type == 'genre' ? 'selected' : '' }}>
                            Genre
                        </option>

                        <option value="playlist" {{ $appSection->section_type == 'playlist' ? 'selected' : '' }}>
                            Playlist
                        </option>
                        <option value="mood" {{ $appSection->section_type == 'mood' ? 'selected' : '' }}>
                            Listen by Mood
                        </option>
                    </optgroup>

                    <optgroup label="Search">
                        <option value="verified_artists"
                            {{ $appSection->section_type == 'verified_artists' ? 'selected' : '' }}>
                            Verified Artists
                        </option>

                        <option value="featured_genres"
                            {{ $appSection->section_type == 'featured_genres' ? 'selected' : '' }}>
                            Featured Genres
                        </option>

                        <option value="featured_playlists"
                            {{ $appSection->section_type == 'featured_playlists' ? 'selected' : '' }}>
                            Featured Playlists
                        </option>
                    </optgroup>
                </select>
            </div>

            <div class="mb-3" id="genre-wrapper" style="display:none;">
                <label class="form-label">Genre</label>

                <select name="genre_id" class="form-control">
                    <option value="">-- Select Genre --</option>

                    @foreach ($genres as $genre)
                        <option value="{{ $genre->id }}"
                            {{ $appSection->section_type === 'genre' && $appSection->reference_id == $genre->id ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3" id="playlist-wrapper" style="display:none;">
                <label class="form-label">Playlist</label>

                <select name="playlist_id" class="form-control">
                    <option value="">-- Select Playlist --</option>

                    @foreach ($playlists as $playlist)
                        <option value="{{ $playlist->id }}"
                            {{ $appSection->section_type === 'playlist' && $appSection->reference_id == $playlist->id ? 'selected' : '' }}>
                            {{ $playlist->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order</label>

                <input type="number" name="sort_order" value="{{ old('sort_order', $appSection->sort_order) }}"
                    class="form-control" required>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active"
                    {{ $appSection->is_active ? 'checked' : '' }}>

                <label for="active" class="form-check-label">
                    Active
                </label>
            </div>

            <div class="mb-3">
                <label class="form-label">Items Limit</label>

                <input type="number" name="limit" class="form-control" value="{{ old('limit', $appSection->limit) }}"
                    min="1" max="100">
            </div>

            <button type="submit" class="btn btn-success">
                Update
            </button>

            <a href="{{ route('admin.app-sections.index') }}" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>
@endsection
