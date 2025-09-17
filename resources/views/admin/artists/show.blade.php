@extends('layouts.admin')

@section('content')
<div class="container">
    <a href="{{ route('admin.artists.index') }}" class="btn btn-link mb-3">← Back to artists</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Artist: {{ $artist->display_name ?? '—' }}</h4>

            <div>
                @if($artist->verified)
                    <span class="badge bg-success">Verified</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <h6>Artist info</h6>
                    <p><strong>Display Name:</strong> {{ $artist->display_name ?? '—' }}</p>
                    <p><strong>Slug / handle:</strong> {{ $artist->slug ?? '—' }}</p>
                    <p><strong>Verified:</strong> {{ $artist->verified ? 'Yes' : 'No' }}</p>

                    @if(!empty($artist->meta) && is_array($artist->meta))
                        <hr>
                        <h6>Meta</h6>
                        @foreach($artist->meta as $k => $v)
                            <p class="mb-1"><strong>{{ ucfirst(str_replace('_',' ', $k)) }}:</strong>
                                @if(is_array($v) || is_object($v))
                                    <pre class="small mb-0">{{ json_encode($v, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ $v }}
                                @endif
                            </p>
                        @endforeach
                    @endif
                </div>

                <div class="col-md-4">
                    <h6>Bio</h6>
                    <div class="p-2 border rounded bg-light text-dark" style="min-height:120px;">
                        @if(!empty($artist->meta['bio']))
                            {!! nl2br(e($artist->meta['bio'])) !!}
                        @else
                            <span class="text-muted">No bio provided</span>
                        @endif
                    </div>

                    <hr>

                    <h6 class="mt-3">User (app account)</h6>
                    @if($artist->user)
                        <p><strong>Name:</strong> {{ $artist->user->name }}</p>
                        <p><strong>Email:</strong> {{ $artist->user->email }}</p>
                        <p><strong>Role:</strong> {{ $artist->user->role ?? '—' }}</p>
                        <p><strong>User ID:</strong> {{ $artist->user->id }}</p>
                        <a href="{{ route('admin.users.show', $artist->user->id) }}" class="btn btn-sm btn-outline-primary">Open user</a>
                    @else
                        <div class="alert alert-warning">No user found for this artist (user_id: {{ $artist->user_id }})</div>
                    @endif
                </div>

                <div class="col-md-4">
                    <h6>Timestamps</h6>
                    <p><strong>Applied At:</strong> {{ optional($artist->created_at)->toDayDateTimeString() ?? '—' }}</p>
                    <p><strong>Updated At:</strong> {{ optional($artist->updated_at)->toDayDateTimeString() ?? '—' }}</p>

                    <hr>

                    <h6>Admin actions</h6>
                    <div class="d-flex flex-column gap-2">
                        <!-- Approve -->
                        <form action="{{ route('admin.artists.approve', $artist) }}" method="POST" onsubmit="return confirm('Approve this artist? They will be granted the artist role.');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100" {{ $artist->verified ? 'disabled' : '' }}>
                                Approve Artist
                            </button>
                        </form>

                        <!-- Reject (toggle reason) -->
                        <button id="showReject" type="button" class="btn btn-outline-danger w-100">
                            Reject / Send reason
                        </button>

                        <form id="rejectForm" action="{{ route('admin.artists.reject', $artist) }}" method="POST" style="display:none;">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2 mt-2">
                                <label for="reason" class="form-label small">Rejection reason (optional)</label>
                                <textarea name="reason" id="reason" rows="4" class="form-control" placeholder="Explain why this was rejected or request changes..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">Reject Artist</button>
                                <button type="button" id="cancelReject" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>

                        <form action="{{ route('admin.artists.show', $artist) }}" method="GET" class="mt-2">
                            <button type="button" id="resendMail" class="btn btn-outline-primary w-100">
                                Resend notification email
                            </button>
                        </form>
                    </div>
                </div>
            </div> <!-- row -->
        </div> <!-- card-body -->
    </div> <!-- card -->

    {{-- Documents / ID images --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Documents & ID Images</h5>
        </div>
        <div class="card-body">
            @php
                $meta = is_array($artist->meta) ? $artist->meta : (is_object($artist->meta) ? (array)$artist->meta : []);
                $idFront = $meta['id_front'] ?? null;
                $idBack = $meta['id_back'] ?? null;
                // other possible keys: 'id_type', 'id_number', 'avatar', etc.
                $avatar = $artist->profile_image ?? null;
            @endphp

            <div class="row">
                <div class="col-md-4 text-center">
                    <h6>Avatar</h6>
                    @if($avatar)
                        <a href="{{ asset('storage/' . ltrim($avatar, '/')) }}" target="_blank">
                            <img src="{{ asset('storage/' . ltrim($avatar, '/')) }}" alt="Avatar" class="img-fluid rounded shadow-sm" style="max-height: 160px;">
                        </a>
                        <div class="mt-2"><a href="{{ asset('storage/' . ltrim($avatar, '/')) }}" target="_blank">Open avatar</a></div>
                    @else
                        <div class="text-muted">No avatar uploaded</div>
                    @endif
                </div>

                <div class="col-md-4 text-center">
                    <h6>ID - Front</h6>
                    @if($idFront)
                        <a href="{{ asset('storage/' . ltrim($idFront, '/')) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . ltrim($idFront, '/')) }}" alt="ID Front" class="img-fluid rounded shadow-sm" style="max-height: 160px;">
                        </a>
                        <div class="mt-2"><a href="{{ asset('storage/' . ltrim($idFront, '/')) }}" target="_blank">Open full image</a></div>
                    @else
                        <div class="text-muted">No front ID uploaded</div>
                    @endif
                </div>

                <div class="col-md-4 text-center">
                    <h6>ID - Back</h6>
                    @if($idBack)
                        <a href="{{ asset('storage/' . ltrim($idBack, '/')) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . ltrim($idBack, '/')) }}" alt="ID Back" class="img-fluid rounded shadow-sm" style="max-height: 160px;">
                        </a>
                        <div class="mt-2"><a href="{{ asset('storage/' . ltrim($idBack, '/')) }}" target="_blank">Open full image</a></div>
                    @else
                        <div class="text-muted">No back ID uploaded</div>
                    @endif
                </div>
            </div>

            {{-- Optional extra info if present --}}
            <div class="mt-3">
                @if(!empty($meta['id_type']) || !empty($meta['id_number']))
                    <p><strong>ID type:</strong> {{ $meta['id_type'] ?? '—' }}</p>
                    <p><strong>ID number:</strong> {{ $meta['id_number'] ?? '—' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Optional: show recent songs or uploads for this artist if you have relationship --}}
    @if(method_exists($artist, 'songs') && $artist->songs()->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Uploaded songs</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($artist->songs()->latest()->limit(20)->get() as $song)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $song->title }}</strong>
                                <div class="small text-muted">{{ $song->artist ?? '—' }} • {{ optional($song->created_at)->toDateString() }}</div>
                            </div>
                            <div>
                                <a href="{{ route('admin.songs.edit', $song) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showBtn = document.getElementById('showReject');
        const cancelBtn = document.getElementById('cancelReject');
        const form = document.getElementById('rejectForm');
        const resend = document.getElementById('resendMail');

        if (showBtn) {
            showBtn.addEventListener('click', () => {
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
                showBtn.style.display = form.style.display === 'none' ? 'inline-block' : 'none';
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                form.style.display = 'none';
                showBtn.style.display = 'inline-block';
            });
        }

        if (resend) {
            resend.addEventListener('click', async () => {
                // quick client feedback - performs a POST to resend via AJAX endpoint if you add one.
                // If you don't have an endpoint, this can be wired later.
                alert('Resend email feature not implemented yet on client. Implement route and fetch to call it.');
            });
        }
    });
</script>
@endsection
