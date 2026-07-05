@extends('layouts.admin')

@section('content')
    <div class="container">

        <h2 class="mb-4">App Settings</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <h4 class="mt-4 mb-3">General</h4>

            @foreach ($groupedRegistry['General'] ?? [] as $key => $setting)
                @include(
                    'admin.settings.fields.' . $setting['type'],
                    compact('key', 'setting', 'settings'))
            @endforeach

            <hr>


            <h4 class="mt-4 mb-3">Feature Flags</h4>

            @foreach ($groupedRegistry['Features'] ?? [] as $key => $setting)
                @include(
                    'admin.settings.fields.' . $setting['type'],
                    compact('key', 'setting', 'settings'))
            @endforeach

            <hr>

            <h4 class="mt-4 mb-3">Limits</h4>

            @foreach ($groupedRegistry['Limits'] ?? [] as $key => $setting)
                @include(
                    'admin.settings.fields.' . $setting['type'],
                    compact('key', 'setting', 'settings'))
            @endforeach

            <h4 class="mt-4 mb-3">Maintenance</h4>

            @foreach ($groupedRegistry['Maintenance'] ?? [] as $key => $setting)
                @include(
                    'admin.settings.fields.' . $setting['type'],
                    compact('key', 'setting', 'settings'))
            @endforeach

            <hr>

            <h4 class="mt-4 mb-3">Version Control</h4>

            @foreach ($groupedRegistry['Version'] ?? [] as $key => $setting)
                @include(
                    'admin.settings.fields.' . $setting['type'],
                    compact('key', 'setting', 'settings'))
            @endforeach

            <button type="submit" class="btn btn-success">
                Save Settings
            </button>
        </form>

        <hr>


    </div>
@endsection
