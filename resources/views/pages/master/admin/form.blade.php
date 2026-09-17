<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? __('form.title.update.admin') : __('form.title.create.admin') }}</x-slot:title>

    <main class="main-table-container" x-data>
        <section class="heading">
            <div>
                <h1>{{ $type == 'update' ? __('form.title.update.admin') : __('form.title.create.admin') }}</h1>
                <p>{{ $type == 'update' ? 'Update admin information' : 'Register a new admin' }}</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            @csrf

            @if ($type == 'update')
                @method('put')
            @endif

            <div class="content-card p-0 overflow-hidden">
                <x-picture-upload :data="$data" :type="$type" />

                <div class="p-8 flex flex-col">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="name">{{ __('form.labels.name') }}</label>
                            <input type="text" name="name" id="name" class="custom-input"
                                placeholder="{{ __('form.placeholders.name') }}" value="{{ $data->name ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('name')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="nipp">{{ __('form.labels.nipp') }}</label>
                            <input type="text" name="nipp" id="nipp" class="custom-input"
                                placeholder="{{ __('form.placeholders.nipp') }}" value="{{ $data->nipp ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('nipp')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="niptk">{{ __('form.labels.niptk') }}</label>
                            <input type="text" name="niptk" id="niptk" class="custom-input"
                                placeholder="{{ __('form.placeholders.niptk') }}" value="{{ $data->niptk ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('niptk')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 mb-3">{{ __('form.labels.address._title') ?? 'Address' }}</h3>
                        <x-address-fields :data="$data" />
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="email">{{ __('form.labels.email') }}</label>
                            <input type="text" name="email" id="email" class="custom-input"
                                placeholder="{{ __('form.placeholders.email') }}" value="{{ $data->email ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            <small class="helper">{{ __('form.helpers.incase-sensitive') }}</small>
                            @error('email')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="password">{{ __('form.labels.password') }}</label>
                            <input type="password" name="password" id="password" class="custom-input"
                                placeholder="{{ __('form.placeholders.password') }}" />
                            <small class="helper">{{ __('form.helpers.password') }}</small>
                            @error('password')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="password_confirmation">{{ __('form.labels.password_confirmation') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="custom-input"
                                placeholder="{{ __('form.placeholders.password_confirmation') }}" />
                            @error('password_confirmation')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-8">
                            {{ $type == 'update' ? __('form.buttons.update') : __('form.buttons.add') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</x-app-layout>
