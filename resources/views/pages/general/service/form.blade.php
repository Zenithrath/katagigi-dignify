@extends('layouts.main-layout')

@section('_title', $type == 'update' ? __('form.title.update.service') : __('form.title.create.service'))
@section('header')
    <x-main-header title="{{ __('features.service') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="GENERAL.SERVICE" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('services.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-icons.chevron-left />
            </a>
            <h1>
                {{ $type == 'update' ? __('form.title.update.service') : __('form.title.create.service') }}
            </h1>
        </div>

        @if (Session::has('error'))
            <div class="mb-8">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        @if (Session::has('success'))
            <div class="mb-8">
                <x-alerts.success message="{{ Session::get('success') }}" />
            </div>
        @endif

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            <div class="content-card">
                @csrf

                @if ($type == 'update')
                    @method('put')
                @endif

                <div class="input-container">
                    <div class="input-group">
                        <label for="name">{{ __('form.labels.service_name') }}</label>
                        <input type="text" name="name" id="name"
                            placeholder="{{ __('form.placeholders.service_name') }}"
                            value="{{ $data->name ?? (old('name') ?? '') }}" required />
                        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                        @error('name')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="description">{{ __('form.labels.service_description') }}</label>
                        <textarea name="description" id="description" cols="30" rows="10"
                            placeholder="{{ __('form.placeholders.service_description') }}">{{ $data->description ?? (old('description') ?? '') }}</textarea>
                        <small class="helper">{{ __('form.helpers.service_description') }}</small>
                        @error('description')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1 input-group">
                            <label for="lower_price">{{ __('form.labels.lower_price') }}</label>
                            <input type="number" name="lower_price" id="lower_price"
                                placeholder="{{ __('form.placeholders.lower_price') }}"
                                value="{{ $data->lower_price ?? (old('lower_price') ?? '') }}" required />
                            <small class="helper">{{ __('form.helpers.numeric') }}</small>
                            @error('lower_price')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="flex-1 input-group">
                            <label for="upper_price">{{ __('form.labels.upper_price') }}</label>
                            <input type="number" name="upper_price" id="upper_price"
                                placeholder="{{ __('form.placeholders.upper_price') }}"
                                value="{{ $data->upper_price ?? (old('upper_price') ?? '') }}" required />
                            <small class="helper">{{ __('form.helpers.numeric') }}</small>
                            @error('upper_price')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="doctor_commision">{{ __('form.labels.doctor_commision') }}</label>
                        <div class="relative w-full">
                            <input type="number" name="doctor_commision" id="doctor_commision" class="w-full"
                                placeholder="{{ __('form.placeholders.commision') }}" min="0" max="100"
                                value="{{ $data->doctor_commision ?? (old('doctor_commision') ?? '') }}" required />
                            <span class="absolute px-4 h-full flex items-center right-0 top-0">%</span>
                        </div>
                        <small class="helper">{{ __('form.helpers.numeric') }}</small>
                        @error('doctor_commision')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="category_id">{{ __('form.labels.service_category') }}</label>
                        <select name="category_id" id="category_id" class="selectable">
                            <option value="" disabled selected>
                                {{ __('form.placeholders.service_category') }}
                            </option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $data->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('category_id')
                            <small class="error">{{ $message }}</small>
                        @enderror

                        <small class="helper">{{ __('form.helpers.service_category') }}
                            <a href="{{ route('categories.create', ['redirect' => 'services/create']) }}"
                                class="clickable">{{ __('form.actions.create_category') }}</a>
                        </small>
                    </div>
                </div>

                <input type="submit" value="{{ $type == 'update' ? __('form.actions.update') : __('form.actions.save') }}"
                    class="clickable-primary py-2 px-4 mt-4 rounded-md w-full" />
            </div>
        </form>
    </main>
@endsection

@pushOnce('scripts')
@endPushOnce
