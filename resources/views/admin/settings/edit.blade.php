@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">App Settings</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">
                Home Greeting
            </label>

            <input
                type="text"
                name="home_greeting"
                value="{{ $settings['home_greeting'] ?? '' }}"
                class="form-control"
            >
        </div>

        <button
            type="submit"
            class="btn btn-success"
        >
            Save Settings
        </button>
    </form>

</div>
@endsection