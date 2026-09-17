<div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
    <div class="input-group">
        <label for="village">{{ __('form.labels.village') }}</label>
        <input type="text" name="village" id="village" class="custom-input"
            placeholder="{{ __('form.placeholders.village') }}" value="{{ $data->village ?? '' }}" />
        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
        @error('village')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-group">
        <label for="street">{{ __('form.labels.street') }}</label>
        <input type="text" name="street" id="street" class="custom-input"
            placeholder="{{ __('form.placeholders.street') }}"
            value="{{ $data->street ?? '' }}" />
        <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,/-']) }}</small>
        @error('street')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-group">
        <label for="zip_code">{{ __('form.labels.zipcode') }}</label>
        <input type="text" name="zip_code" id="zip_code" class="custom-input"
            placeholder="{{ __('form.placeholders.zipcode') }}"
            value="{{ $data->zip_code ?? '' }}" />
        @error('zip_code')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
    <div class="input-group">
        <label for="tonarigumi">{{ __('form.labels.tonarigumi') }}</label>
        <input type="text" name="tonarigumi" id="tonarigumi" class="custom-input"
            placeholder="{{ __('form.placeholders.tonarigumi') }}"
            value="{{ $data->tonarigumi ?? '' }}" />
        @error('tonarigumi')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-group">
        <label for="district">{{ __('form.labels.district') }}</label>
        <input type="text" name="district" id="district" class="custom-input"
            placeholder="{{ __('form.placeholders.district') }}"
            value="{{ $data->district ?? '' }}" />
        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
        @error('district')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="input-group">
        <label for="regency">{{ __('form.labels.city') }}</label>
        <input type="text" name="regency" id="regency" class="custom-input"
            placeholder="{{ __('form.placeholders.city') }}" value="{{ $data->regency ?? '' }}" />
        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
        @error('regency')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="input-group">
    <label for="province">{{ __('form.labels.state') }}</label>
    <input type="text" name="province" id="province" class="custom-input"
        placeholder="{{ __('form.placeholders.state') }}" value="{{ $data->province ?? '' }}" />
    <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
    @error('province')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>
