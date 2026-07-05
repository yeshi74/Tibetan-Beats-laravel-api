@extends('layouts.admin')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="mb-1">
                    Support Message
                </h2>

                <small class="text-muted">
                    Submitted {{ $supportMessage->created_at->diffForHumans() }}
                </small>

            </div>

            <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">
                ← Back
            </a>

        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">

            <div class="card-body">

                <div class="row mb-4">

                    <div class="col-md-6">

                        <label class="fw-bold">
                            User
                        </label>

                        <p class="mb-0">
                            {{ $supportMessage->user->name }}
                        </p>

                        <small class="text-muted">
                            {{ $supportMessage->user->email }}
                        </small>

                    </div>

                    <div class="col-md-6">

                        <label class="fw-bold">
                            Status
                        </label>

                        <p>

                            @if ($supportMessage->status == 'open')
                                <span class="badge bg-danger">
                                    Open
                                </span>
                            @else
                                <span class="badge bg-success">
                                    Resolved
                                </span>
                            @endif

                        </p>

                    </div>

                </div>

                <hr>

                <div class="mb-4">

                    <label class="fw-bold">
                        Subject
                    </label>

                    <div class="mt-2">
                        {{ $supportMessage->subject }}
                    </div>

                </div>

                <div class="mb-4">

                    <label class="fw-bold">
                        Message
                    </label>

                    <div class="border rounded p-3 mt-2" style="white-space: pre-wrap;">
                        {{ $supportMessage->message }}
                    </div>

                </div>

                @if ($supportMessage->status == 'open')
                    <form method="POST" action="{{ route('admin.support.resolve', $supportMessage) }}">
                        @csrf
                        @method('PATCH')

                        <button class="btn btn-success">
                            ✓ Mark as Resolved
                        </button>

                    </form>
                @endif

            </div>

        </div>

    </div>
@endsection
