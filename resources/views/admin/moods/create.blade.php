@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2 class="mb-4">Add Mood</h2>

        <form action="{{ route('admin.moods.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Mood Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Emoji</label>
                <input type="text" name="emoji" class="form-control" value="{{ old('emoji') }}" placeholder="😌">
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Start Color
                    </label>

                    <input type="color" name="start_color"
                        value="{{ old('start_color', $mood->start_color ?? '#8E24AA') }}"
                        class="w-full h-12 rounded-lg border">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        End Color
                    </label>

                    <input type="color" name="end_color" value="{{ old('end_color', $mood->end_color ?? '#AB47BC') }}"
                        class="w-full h-12 rounded-lg border">
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>

                <select name="is_active" class="form-select">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button class="btn btn-primary">
                Save Mood
            </button>

            <a href="{{ route('admin.moods.index') }}" class="btn btn-secondary">
                Cancel
            </a>

        </form>
    </div>
@endsection
