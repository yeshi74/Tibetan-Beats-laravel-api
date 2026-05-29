@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">Artist Application Review</h2>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="row">

                <div class="col-md-3 text-center">

                    @if($artist->profile_image)
                        <img
                            src="{{ asset('storage/'.$artist->profile_image) }}"
                            class="rounded-circle border shadow"
                            width="150"
                            height="150"
                            style="object-fit:cover;">
                    @endif

                </div>

                <div class="col-md-9">

                    <h3>{{ $artist->display_name }}</h3>

                    <hr>

                    <p>
                        <strong>Real Name:</strong>
                        {{ $artist->real_name ?? '-' }}
                    </p>

                    <p>
                        <strong>Country:</strong>
                        {{ $artist->country ?? '-' }}
                    </p>

                    <p>
                        <strong>Email:</strong>
                        {{ $artist->user->email ?? '-' }}
                    </p>

                </div>

            </div>

        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h4>Biography</h4>

            <p>
                {{ $artist->meta['bio'] ?? '-' }}
            </p>

        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h4>ID Verification</h4>

            <div class="row">

                <div class="col-md-6">

                    <h6>Front ID</h6>

                    @if(!empty($artist->meta['id_front']))
                        <img
                            src="{{ asset('storage/'.$artist->meta['id_front']) }}"
                            class="img-fluid rounded border">
                    @endif

                </div>

                <div class="col-md-6">

                    <h6>Back ID</h6>

                    @if(!empty($artist->meta['id_back']))
                        <img
                            src="{{ asset('storage/'.$artist->meta['id_back']) }}"
                            class="img-fluid rounded border">
                    @endif

                </div>

            </div>

        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <h4>Sample Track</h4>

            @if(!empty($artist->meta['sample_track']))
                <audio controls style="width:100%;">
                    <source
                        src="{{ asset('storage/'.$artist->meta['sample_track']) }}">
                </audio>
            @else
                <p class="text-muted">
                    No sample track uploaded.
                </p>
            @endif

        </div>
    </div>

    <div class="d-flex gap-2">

        <form method="POST"
              action="{{ route('admin.artists.approve', $artist->id) }}">
            @csrf
            @method('PATCH')

            <button class="btn btn-success btn-lg">
                Approve Artist
            </button>
        </form>

        <form method="POST"
              action="{{ route('admin.artists.reject', $artist->id) }}">
            @csrf
            @method('PATCH')

            <button class="btn btn-danger btn-lg">
                Reject Artist
            </button>
        </form>

    </div>

</div>

@endsection