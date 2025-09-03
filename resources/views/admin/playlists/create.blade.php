@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Create Playlist</h2>

    <form action="{{ route('admin.playlists.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label class="form-label">Playlist Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Cover Image</label>
        <input type="file" name="cover_image" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Featured?</label>
        <input type="checkbox" name="is_featured" value="1">
    </div>

    <div class="mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-control">
            <option value="">-- None --</option>
            <option value="festival">Festival</option>
            <option value="sleep">Sleep</option>
            <option value="curated">Curated</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Select Songs</label>
        <select name="songs[]" multiple class="form-control" size="10">
            @foreach($songs as $song)
                <option value="{{ $song->id }}">{{ $song->title }} - {{ $song->artist }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('admin.playlists.index') }}" class="btn btn-secondary">Cancel</a>
</form>

</div>
@endsection
