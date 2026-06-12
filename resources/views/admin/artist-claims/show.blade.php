@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Artist Claim Review</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card p-4 mb-4">

        <h4>Artist Profile</h4>

        <p>
            <strong>Name:</strong>
            {{ $claim->artist?->display_name }}
        </p>

        <hr>

        <h4>Claimant</h4>

        <p>
            <strong>Name:</strong>
            {{ $claim->claimant_name }}
        </p>

        <p>
            <strong>Email:</strong>
            {{ $claim->email }}
        </p>

        <hr>

        <h4>Reason / Proof</h4>

        <div class="border rounded p-3">
            {{ $claim->reason }}
        </div>

        <hr>

        <p>
            <strong>Status:</strong>
            {{ ucfirst($claim->status) }}
        </p>

    </div>

    @if($claim->status === 'pending')

        <form method="POST"
              action="{{ route('admin.artist-claims.approve', $claim) }}"
              class="d-inline">

            @csrf
            @method('PATCH')

            <button type="submit"
                    class="btn btn-success">
                Approve
            </button>
        </form>

        <form method="POST"
              action="{{ route('admin.artist-claims.reject', $claim) }}"
              class="d-inline">

            @csrf
            @method('PATCH')

            <input
                type="text"
                name="admin_notes"
                class="form-control mt-3 mb-3"
                placeholder="Reason for rejection">

            <button type="submit"
                    class="btn btn-danger">
                Reject
            </button>
        </form>

    @endif

</div>
@endsection