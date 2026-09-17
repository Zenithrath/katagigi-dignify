@extends('layouts.main-layout')

@section('_title', $type == 'update' ? __('form.title.update.patient') : __('form.title.create.patient'))
@section('header')
    <x-main-header title="{{ __('features.patient') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="PATIENT.MASTER" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('patients.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-icons.chevron-left />
            </a>
            <h1> {{ $type == 'update' ? __('form.title.update.patient') : __('form.title.create.patient') }} </h1>
        </div>

        @if (Session::has('error'))
            <div class="mb-16">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            <div class="picture-container">
                {{-- <div class="profile-picture -top-12" x-data="pictureState">
                    <div class="relative w-full h-full">
                        <img id="profile-preview" x-show="isProfilePreviewMode"
                            src="{{ $type == 'update' && (isset($data->picture) || old('picture')) ? asset('storage/' . $data->picture) : '' }}"
                            alt="" srcset="" class="absolute w-full" />
                        <div class="absolute flex items-center justify-center w-full h-full">
                            <label>
                                <input type="file" name="picture" id="profile"
                                    @change="showProfilePreview(event, 'profile-preview')" />
                                <div class="picture-action browse">
                                    <x-icons.camera-plus />
                                </div>
                            </label>
                        </div>
                    </div>
                </div> --}}

                <div class="content-card">
                    @csrf

                    @if ($type == 'update')
                        @method('put')
                    @endif


                    @error('picture')
                        <small class="error">{{ $message }}</small>
                    @enderror

                    <div class="input-container">
                        <div class="input-group">
                            <label for="name">{{ __('form.labels.name') }}</label>
                            <input type="text" name="name" id="name"
                                placeholder="{{ __('form.placeholders.name') }}" value="{{ $data->name ?? '' }}" required />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('name')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="email">{{ __('form.labels.email') }}</label>
                            <input type="text" name="email" id="email"
                                placeholder="{{ __('form.placeholders.email') }}" value="{{ $data->email ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            @error('email')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="payment_email">{{ __('form.labels.payment_email') }}</label>
                            <div class="relative flex">
                                <input type="text" name="payment_email" id="payment_email" class="flex-1"
                                    placeholder="{{ __('form.placeholders.payment_email') }}"
                                    value="{{ $data->payment_email ?? '' }}" />
                                <button class="absolute h-full right-4 text-xs font-semibold text-brand-500"
                                    @click.prevent="copyEmail()">
                                    {{ __('form.actions.use_email') }}
                                </button>
                            </div>
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            @error('payment_email')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="phone">{{ __('form.labels.phone') }}</label>
                            <div class="relative w-full">
                                <div class="absolute left-0 flex items-center px-4 h-full text-sm">+62</div>
                                <input type="tel" name="phone" id="phone" class="w-full pl-14"
                                    placeholder="{{ __('form.placeholders.phone') }}"
                                    value="{{ preg_replace('/^62/', '', $data->phone) ?? '' }}" />
                            </div>
                            <small class="helper">{{ __('form.helpers.phone') }}</small>
                            @error('phone')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="birthdate">{{ __('form.labels.birthdate') }}</label>
                            <input type="date" name="birthdate" id="birthdate"
                                value="{{ $data->birthdate ?? '' }}" />
                            @error('birthdate')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="input-group flex-1">
                                <label for="religion">{{ __('form.labels.religion') }}</label>
                                <select id="religion" name="religion" autocomplete="religion" class="selectable">
                                    <option {{ !isset($data->gender) || !$data->gender ? 'selected' : '' }} disabled>
                                        {{ __('form.placeholders.religion') }}
                                    </option>
                                    <option value="ISLAM" {{ $data->religion === 'ISLAM' ? 'selected' : '' }}>
                                        {{ __('form.labels.islam') }}
                                    </option>
                                    <option value="CHRISTIANITY"
                                        {{ $data->religion === 'CHRISTIANITY' ? 'selected' : '' }}>
                                        {{ __('form.labels.christianity') }}
                                    </option>
                                    <option value="CATHOLIC" {{ $data->religion === 'CATHOLIC' ? 'selected' : '' }}>
                                        {{ __('form.labels.catholic') }}
                                    </option>
                                    <option value="BUDDHISM" {{ $data->religion === 'BUDDHISM' ? 'selected' : '' }}>
                                        {{ __('form.labels.buddhism') }}
                                    </option>
                                    <option value="HINDUISM" {{ $data->religion === 'HINDUISM' ? 'selected' : '' }}>
                                        {{ __('form.labels.hinduism') }}
                                    </option>
                                    <option value="KONGHUCHU" {{ $data->religion === 'KONGHUCHU' ? 'selected' : '' }}>
                                        {{ __('form.labels.konghuchu') }}
                                    </option>
                                    <option value="OTHER" {{ $data->religion === 'OTHER' ? 'selected' : '' }}>
                                        {{ __('form.labels.other_religion') }}
                                    </option>
                                </select>
                                @error('religion')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="gender">{{ __('form.labels.gender') }}</label>
                                <select id="gender" name="gender" autocomplete="gender-name" class="selectable">
                                    <option
                                        {{ !isset($data->gender) || !$data->gender || $data->gender === '' ? 'selected' : '' }}
                                        disabled>
                                        {{ __('form.placeholders.gender') }}
                                    </option>
                                    <option value="MALE" {{ $data->gender === 'MALE' ? 'selected' : '' }}>
                                        {{ __('form.labels.male') }}
                                    </option>
                                    <option value="FEMALE" {{ $data->gender === 'FEMALE' ? 'selected' : '' }}>
                                        {{ __('form.labels.female') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="border-b border-slate-700"></div>

                        <div class="input-group">
                            <label for="village">{{ __('form.labels.village') }}</label>
                            <input type="text" name="village" id="village"
                                placeholder="{{ __('form.placeholders.village') }}"
                                value="{{ $data->village ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                            @error('village')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="flex flex-col md:flex-row gap-2">
                            <div class="flex-1 input-group">
                                <label for="street">{{ __('form.labels.street') }}</label>
                                <input type="text" name="street" id="street"
                                    placeholder="{{ __('form.placeholders.street') }}"
                                    value="{{ $data->street ?? '' }}" />
                                <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                @error('street')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="flex flex-col md:flex-row gap-2">
                                <div class="input-group">
                                    <label for="zip_code">{{ __('form.labels.zipcode') }}</label>
                                    <input type="text" name="zip_code" id="zip_code"
                                        placeholder="{{ __('form.placeholders.zipcode') }}"
                                        value="{{ $data->zip_code ?? '' }}" />
                                    <small
                                        class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '-']) }}</small>
                                    @error('zip_code')
                                        <small class="error">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="input-group">
                                    <label for="tonarigumi">{{ __('form.labels.tonarigumi') }}</label>
                                    <input type="text" name="tonarigumi" id="tonarigumi"
                                        placeholder="{{ __('form.placeholders.tonarigumi') }}"
                                        value="{{ $data->tonarigumi ?? '' }}" />
                                    <small
                                        class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                    @error('tonarigumi')
                                        <small class="error">{{ $message }}</small>
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
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="regency">{{ __('form.labels.city') }}</label>
                            <input type="text" name="regency" id="regency"
                                placeholder="{{ __('form.placeholders.city') }}" value="{{ $data->regency ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                            @error('regency')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="province">{{ __('form.labels.state') }}</label>
                            <input type="text" name="province" id="province"
                                placeholder="{{ __('form.placeholders.state') }}"
                                value="{{ $data->province ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                            @error('province')
                                <small class="error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div x-data="{ showSosmed: false }">
                        <div class="my-2">
                            <input id="toggleSosmed" type="checkbox" class="auth-checkbox mr-1" x-model="showSosmed">
                            <span class="auth-checkbox-caption">{{ __('patient.master.form.labels.needed_sosmed') }}</span>
                        </div>

                        <div class="flex flex-col md:flex-row gap-2" x-show="showSosmed">
                            <div class="flex-1 input-group">
                                <label for="sosmed">{{ __('form.labels.sosmed.facebook') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->facebook ?? '' }}" />
                                <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                @error('sosmed')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="flex-1 input-group">
                                <label for="sosmed">{{ __('form.labels.sosmed.instagram') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->instagram ?? '' }}" />
                                <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                @error('sosmed')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="flex-1 input-group">
                                <label for="sosmed">{{ __('form.labels.sosmed.tiktok') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->tiktok ?? '' }}" />
                                <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                @error('sosmed')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="flex-1 input-group">
                                <label for="sosmed">{{ __('form.labels.sosmed.twitter') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->twitter ?? '' }}" />
                                <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                                @error('sosmed')
                                    <small class="error">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <input type="submit"
                        value="{{ $type == 'update' ? __('form.actions.update') : __('form.actions.save') }}"
                        class="clickable-primary py-2 px-4 mt-4 rounded-md w-full" />
                </div>
            </div>
        </form>
    </main>
@endsection

@pushOnce('scripts')
    <script type="text/javascript">
        const pictureState = {
            isProfilePreviewMode: false,
            init() {
                if ("{{ $data->picture }}" != "") {
                    this.isProfilePreviewMode = true;
                }
            },
            showProfilePreview(event, targetID) {
                if (event.target.files.length <= 0) return;
                let src = URL.createObjectURL(event.target.files[0]);
                let preview = document.getElementById(targetID);
                this.isProfilePreviewMode = true;
                preview.src = src;
                preview.style.display = "block";
            }
        };

        function copyEmail() {
            let email = document.getElementById('email');
            let payment_email = document.getElementById('payment_email');
            payment_email.value = email.value;
        }
    </script>
@endPushOnce
