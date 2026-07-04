<div class="mb-3">

    <label class="form-label">
        {{ $setting['label'] }}
    </label>

    <input
        type="number"
        name="{{ $key }}"
        class="form-control"
        value="{{ $settings[$key] ?? $setting['default'] }}"
    >

    @if(!empty($setting['description']))
        <small class="text-muted">
            {{ $setting['description'] }}
        </small>
    @endif

</div>