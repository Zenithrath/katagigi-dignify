{{-- x-address-fields: address fieldset (village, street, zip, tonarigumi, district, regency, province)
     Props: $data (model with address fields) --}}
<div class="input-group">
    <label for="village">{{ __('form.labels.village') }}</label>
    <input type="text" name="village" id="village"
        placeholder="{{ __('form.placeholders.village') }}" value="{{ $data->village ?? '' }}" />
    <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
    @error('village')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>

<div class="flex flex-col sm:flex-row gap-2">
    <div class="flex-1 input-group">
        <label for="street">{{ __('form.labels.street') }}</label>
        <input type="text" name="street" id="street"
            placeholder="{{ __('form.placeholders.street') }}"
            value="{{ $data->street ?? '' }}" />
        <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,/-']) }}</small>
        @error('street')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <div class="input-group">
            <label for="zip_code">{{ __('form.labels.zipcode') }}</label>
            <input type="text" name="zip_code" id="zip_code"
                placeholder="{{ __('form.placeholders.zipcode') }}"
                value="{{ $data->zip_code ?? '' }}" />
            <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-']) }}</small>
            @error('zip_code')
                <small class="danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="input-group">
            <label for="tonarigumi">{{ __('form.labels.tonarigumi') }}</label>
            <input type="text" name="tonarigumi" id="tonarigumi"
                placeholder="{{ __('form.placeholders.tonarigumi') }}"
                value="{{ $data->tonarigumi ?? '' }}" />
            <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
            @error('tonarigumi')
                <small class="danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>

<div class="input-group">
    <label for="district">{{ __('form.labels.district') }}</label>
    <input type="text" name="district" id="district"
        placeholder="{{ __('form.placeholders.district') }}"
        value="{{ $data->district ?? '' }}" />
    <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
    @error('district')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>

<div class="input-group">
    <label for="regency">{{ __('form.labels.city') }}</label>
    <input type="text" name="regency" id="regency"
        placeholder="{{ __('form.placeholders.city') }}" value="{{ $data->regency ?? '' }}" />
    <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
    @error('regency')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>

<div class="input-group">
    <label for="province">{{ __('form.labels.state') }}</label>
    <input type="text" name="province" id="province"
        placeholder="{{ __('form.placeholders.state') }}" value="{{ $data->province ?? '' }}" />
    <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
    @error('province')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>
