@extends('layouts.admin')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h2 class="mb-1">
                    Support Inbox
                </h2>

                <small class="text-muted">
                    Messages submitted from the TibetTune app
                </small>
            </div>

            <span class="badge bg-primary fs-6">
                {{ $messages->total() }} Messages
            </span>

        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>User</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="120"></th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($messages as $message)
                            <tr>

                                <td>

                                    <strong>
                                        {{ $message->user->name }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ $message->user->email }}
                                    </small>

                                </td>

                                <td>

                                    <strong>
                                        {{ $message->subject }}
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ \Illuminate\Support\Str::limit($message->message, 60) }}
                                    </small>

                                </td>

                                <td>

                                    @if ($message->status == 'open')
                                        <span class="badge bg-danger">
                                            Open
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Resolved
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    {{ $message->created_at->diffForHumans() }}

                                </td>

                                <td>

                                    <a href="{{ route('admin.support.show', $message) }}" class="btn btn-sm btn-primary">
                                        Open
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center py-5">

                                    <h5>No support messages yet</h5>

                                    <p class="text-muted mb-0">
                                        Messages sent from the mobile app will appear here.
                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-3">

            {{ $messages->links() }}

        </div>

    </div>
@endsection
