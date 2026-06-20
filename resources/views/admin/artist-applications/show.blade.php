@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">
        Artist Application #{{ $application->id }}
    </h2>

    <div class="card">
        <div class="card-body">

            <h4>User Information</h4>

            <p>
                <strong>Name:</strong>
                {{ $application->user?->name }}
            </p>

            <p>
                <strong>Email:</strong>
                {{ $application->user?->email }}
            </p>

            <hr>

            <h4>Artist Information</h4>

            <p>
                <strong>Display Name:</strong>
                {{ $application->display_name }}
            </p>

            <p>
                <strong>Real Name:</strong>
                {{ $application->real_name }}
            </p>

            <p>
                <strong>Country:</strong>
                {{ $application->country }}
            </p>

            <p>
                <strong>Status:</strong>
                {{ ucfirst($application->status) }}
            </p>

            @if(!empty($application->meta['bio']))
                <p>
                    <strong>Bio:</strong><br>
                    {{ $application->meta['bio'] }}
                </p>
            @endif

            <hr>

            <h4>Profile Image</h4>

            @if($application->profile_image)
                <img
                    src="{{ asset('storage/' . $application->profile_image) }}"
                    style="max-width:200px"
                    class="img-thumbnail"
                >
            @else
                <p>No profile image uploaded.</p>
            @endif

            <hr>

            <h4>ID Verification</h4>

            @if(!empty($application->meta['id_front']))
                <p>
                    <strong>ID Front:</strong><br>
                    <a href="{{ asset('storage/' . $application->meta['id_front']) }}"
                       target="_blank">
                        View ID Front
                    </a>
                </p>
            @endif

            @if(!empty($application->meta['id_back']))
                <p>
                    <strong>ID Back:</strong><br>
                    <a href="{{ asset('storage/' . $application->meta['id_back']) }}"
                       target="_blank">
                        View ID Back
                    </a>
                </p>
            @endif

            <hr>

            <h4>Sample Track</h4>

            @if(!empty($application->meta['sample_track']))
                <audio controls>
                    <source
                        src="{{ asset('storage/' . $application->meta['sample_track']) }}">
                </audio>
            @else
                <p>No sample track uploaded.</p>
            @endif

            <hr>

            @if($application->status === 'pending')

                <form method="POST"
                      action="{{ route('admin.artist-applications.approve', $application) }}"
                      class="d-inline">
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-success">
                        Approve
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.artist-applications.reject', $application) }}"
                      class="d-inline ms-2">
                    @csrf

                    <input
                        type="text"
                        name="admin_notes"
                        placeholder="Reason for rejection"
                        class="form-control mb-2"
                    >

                    <button
                        type="submit"
                        class="btn btn-danger">
                        Reject
                    </button>
                </form>

            @endif

        </div>
    </div>

</div>
@endsection