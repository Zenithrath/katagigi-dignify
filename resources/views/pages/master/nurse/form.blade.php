<x-app-layout>
    <x-slot:title>{{ 'Dashboard' }}</x-slot:title>

    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('nurses.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-lucide-chevron-left class="w-full h-full" />
            </a>
            <h1> {{ $type == 'update' ? __('master.nurse.form.title.edit') : __('master.nurse.form.title.add') }} </h1>
        </div>

        <div class="content-card p-0">
            <form method="post" enctype="multipart/form-data" action="{{ $action }}">
                @csrf

                @if ($type == 'update')
                    @method('put')
                @endif

                <x-picture-upload :data="$data" :type="$type" />

                <div class="pb-8 px-8 flex flex-col">
                    <div class="input-container">
                        <div class="input-group">
                            <label for="name">{{ __('form.labels.name') }}</label>
                            <input type="text" name="name" id="name"
                                placeholder="{{ __('form.placeholders.name') }}" value="{{ $data->name ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('name')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="nipp">{{ __('form.labels.nipp') }}</label>
                            <input type="text" name="nipp" id="nipp"
                                placeholder="{{ __('form.placeholders.nipp') }}." value="{{ $data->nipp ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('nipp')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="niptk">{{ __('form.labels.niptk') }}</label>
                            <input type="text" name="niptk" id="niptk"
                                placeholder="{{ __('form.placeholders.niptk') }}" value="{{ $data->niptk ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('niptk')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="border-b border-slate-700"></div>

                        <x-address-fields :data="$data" />

                        <div class="border-b border-slate-700"></div>

                        <div class="input-group">
                            <label for="email">{{ __('form.labels.email') }}</label>
                            <input type="text" name="email" id="email"
                                placeholder="{{ __('form.placeholders.email') }}" value="{{ $data->email ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            <small class="helper">{{ __('form.helpers.incase-sensitive') }}</small>
                            @error('email')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- @if ($type == 'create') --}}
                        <div class="input-group">
                            <label for="password">{{ __('form.labels.password') }}</label>
                            <input type="password" name="password" id="password"
                                placeholder="{{ __('form.placeholders.password') }}" />
                            <small class="helper">{{ __('form.helpers.password') }}</small>
                            @error('password')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="password_confirmation">{{ __('form.labels.password_confirmation') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                placeholder="{{ __('form.placeholders.password_confirmation') }}" />
                            @error('password_confirmation')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                        {{-- @endif --}}
                    </div>

                    <input type="submit"
                        value="{{ $type == 'update' ? __('form.buttons.update') : __('form.buttons.add') }}"
                        class="clickable-primary py-2 rounded-md mt-4" />
                </div>
            </form>
        </div>
    </main>


</x-app-layout>
