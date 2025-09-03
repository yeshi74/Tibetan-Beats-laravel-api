@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Genre</h2>

    <form action="{{ route('admin.genres.update', $genre) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Genre Name</label>
            <input type="text" name="name" value="{{ $genre->name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ $genre->description }}</textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featured"
                {{ $genre->is_featured ? 'checked' : '' }}>
            <label for="featured" class="form-check-label">Featured Genre</label>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('admin.genres.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
