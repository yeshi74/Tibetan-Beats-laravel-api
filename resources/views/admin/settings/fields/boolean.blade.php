<div class="form-check mb-3">

    <input
        class="form-check-input"
        type="checkbox"
        id="{{ $key }}"
        name="{{ $key }}"
        value="1"
        {{ !empty($settings[$key]) ? 'checked' : '' }}
    >

    <label
        class="form-check-label"
        for="{{ $key }}"
    >
        {{ $setting['label'] }}
    </label>

    @if(!empty($setting['description']))
        <div>
            <small class="text-muted">
                {{ $setting['description'] }}
            </small>
        </div>
    @endif

</div>