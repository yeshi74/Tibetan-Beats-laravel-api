@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Edit Artist Profile</h2>

    <form method="POST"
      action="{{ route('admin.artists.update', $artist) }}"
      enctype="multipart/form-data">

    @csrf
    @method('PUT')


        <div class="mb-3">
            <label class="form-label">Display Name</label>
            <input type="text"
                   name="display_name"
                   class="form-control"
                   value="{{ old('display_name', $artist->display_name) }}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Real Name</label>
            <input type="text"
                   name="real_name"
                   class="form-control"
                   value="{{ old('real_name', $artist->real_name) }}"
                   >

        </div>

        <div class="mb-3">
            <label class="form-label">Country</label>
            <input type="text"
                   name="country"
                   class="form-control"
                   value="{{ old('country', $artist->country) }}"
                   >
        </div>

        <div class="mb-3">
    <label class="form-label">
        Artist Profile Visibility
    </label>

    <select
        name="show_profile"
        class="form-control"
    >
        <option
            value="1"
            {{ $artist->show_profile ? 'selected' : '' }}
        >
            Visible
        </option>

        <option
            value="0"
            {{ !$artist->show_profile ? 'selected' : '' }}
        >
            Hidden
        </option>
    </select>
</div>

        <div class="mb-3">
            <label class="form-label">Profile Image</label>

            @if($artist->profile_image)

                <div class="mb-3">
                    <img
                        src="{{ asset('storage/'.$artist->profile_image) }}"
                        width="120"
                        class="img-thumbnail">
                </div>

            @endif

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
            Update Artist
        </button>

        <a href="{{ route('admin.artists.show', $artist) }}"
           class="btn btn-secondary">
            Cancel
        </a>

    </form>
</div>
@endsection