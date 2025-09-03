@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Playlist</h2>

    <form action="{{ route('admin.playlists.update', $playlist) }}" method="POST" enctype="multipart/form-data">
        @csrf 
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Playlist Name</label>
            <input type="text" name="name" value="{{ old('name', $playlist->name) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description', $playlist->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Cover Image</label><br>
            @if($playlist->cover_image)
                <img src="{{ asset('storage/' . $playlist->cover_image) }}" 
                     alt="Cover Image" 
                     class="w-32 h-32 object-cover mb-2 rounded">
            @endif
            <input type="file" name="cover_image" class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1"
                {{ old('is_featured', $playlist->is_featured) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_featured">Featured Playlist</label>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
                <option value="">-- None --</option>
                <option value="festival" {{ old('type', $playlist->type) == 'festival' ? 'selected' : '' }}>Festival</option>
                <option value="sleep" {{ old('type', $playlist->type) == 'sleep' ? 'selected' : '' }}>Sleep</option>
                <option value="curated" {{ old('type', $playlist->type) == 'curated' ? 'selected' : '' }}>Curated</option>
            </select>
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
