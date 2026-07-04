<div class="mb-3">

    <label class="form-label">
        {{ $setting['label'] }}
    </label>

    <input
        type="text"
        name="{{ $key }}"
        class="form-control"
        value="{{ $settings[$key] ?? $setting['default'] }}"
        placeholder="{{ $setting['placeholder'] ?? '' }}"
    >

    @if(!empty($setting['description']))
        <small class="text-muted">
            {{ $setting['description'] }}
        </small>
    @endif

</div>