<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? __('form.title.update.category') : __('form.title.create.category') }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ isset($back->redirect) ? url($back->redirect) : route('categories.index') }}"
                class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1>
                {{ $type == 'update' ? __('form.title.update.category') : __('form.title.create.category') }}
            </h1>
        </div>

        <x-flash-alerts />

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            <div class="content-card">
                @csrf

                @if ($type == 'update')
                    @method('put')
                @endif

                <div class="input-container">
                    <div class="input-group">
                        <label for="name">{{ __('form.labels.category_name') }}</label>
                        <input type="text" name="name" id="name"
                            placeholder="{{ __('form.placeholders.category_name') }}"
                            value="{{ $data->name ?? (old('name') ?? '') }}" required />
                        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                        @error('name')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="input-group">
                        <label for="code">{{ __('form.labels.category_code') }}</label>
                        <input type="text" name="code" id="code"
                            placeholder="{{ __('form.placeholders.category_code') }}"
                            value="{{ $data->code ?? (old('code') ?? '') }}" required />
                        <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                        @error('code')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <input type="submit" value="{{ $type == 'update' ? __('form.actions.update') : __('form.actions.save') }}"
                    class="clickable-primary py-2 px-4 mt-4 rounded-md w-full" />
            </div>
        </form>
    </main>


@pushOnce('scripts')
@endPushOnce
</x-app-layout>
