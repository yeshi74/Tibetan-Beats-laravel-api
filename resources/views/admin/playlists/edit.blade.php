@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Playlist</h2>

    <form action="{{ route('admin.playlists.update', $playlist) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Playlist Name</label>
            <input type="text" name="name" value="{{ $playlist->name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ $playlist->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Songs</label>
            <select name="songs[]" multiple class="form-control" size="10">
                @foreach($songs as $song)
                    <option value="{{ $song->id }}" 
                        {{ in_array($song->id, $selectedSongs) ? 'selected' : '' }}>
                        {{ $song->title }} - {{ $song->artist }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('admin.playlists.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
