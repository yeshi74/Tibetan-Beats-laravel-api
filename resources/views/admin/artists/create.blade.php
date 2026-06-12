@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Create Artist Profile</h2>

    <form method="POST"
      action="{{ route('admin.artists.store') }}"
      enctype="multipart/form-data">

        @csrf

        <div class="mb-3">
            <label class="form-label">Display Name</label>
            <input type="text"
                   name="display_name"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Real Name</label>
            <input type="text"
                   name="real_name"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Country</label>
            <input type="text"
                   name="country"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Profile Image</label>

            <input type="file"
                   name="profile_image"
                   class="form-control"
                   accept="image/*">

            <small class="text-muted">
                Recommended: Square image (500x500 or larger)
            </small>
        </div>

        <button type="submit"
                class="btn btn-success">
            Create Artist
        </button>

    </form>
</div>
@endsection