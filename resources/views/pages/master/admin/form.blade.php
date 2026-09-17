@extends('layouts.main-layout')

@section('_title', $type == 'update' ? __('form.title.update.admin') : __('form.title.create.admin'))
@section('header')
    <x-main-header title="{{ __('features.admin') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="MASTER.ADMIN" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="mb-auto px-8 pt-8 pb-12" x-data>
        <div class="flex gap-4 items-center">
            <a href="{{ route('admins.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-icons.chevron-left />
            </a>
            <h1> {{ $type == 'update' ? __('form.title.update.admin') : __('form.title.create.admin') }} </h1>
        </div>

        <div class="content-card p-0">
            <form method="post" enctype="multipart/form-data" action="{{ $action }}">
                @csrf

                @if ($type == 'update')
                    @method('put')
                @endif

                <div class="picture-container" x-data="pictureState">
                    <div class="cover-picture overflow-hidden relative">
                        <img id="cover-preview" x-show="isCoverPreviewMode"
                            src="{{ $type == 'update' && isset($data->cover_picture) ? url('storage/' . $data->cover_picture) : '' }}"
                            alt="" srcset="" />
                        <div class="picture-action-container absolute">
                            <label>
                                <input type="file" name="cover_image" id="cover_image"
                                    @change="showCoverPreview(event, 'cover-preview')" />
                                <div class="picture-action browse">
                                    <x-icons.camera-plus />
                                </div>
                            </label>
                            <button class="picture-action" @click="clearCover(event, 'cover_image', 'cover-preview')">
                                <x-icons.x />
                            </button>
                        </div>
                    </div>
                    <div class="profile-picture top-1/2 md:top-1/2">
                        <div class="relative w-full h-full">
                            <img id="profile-preview" x-show="isProfilePreviewMode"
                                src="{{ $type == 'update' && isset($data->profile_picture) ? url('storage/' . $data->profile_picture) : '' }}"
                                alt="" srcset="" class="absolute w-full" />
                            <div class="absolute flex items-center justify-center w-full h-full">
                                <label>
                                    <input type="file" name="profile_image" id="profile"
                                        @change="showProfilePreview(event, 'profile-preview')" />
                                    <div class="picture-action browse">
                                        <x-icons.camera-plus />
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    @error('cover_image')
                        <div class="ml-36 md:ml-48">
                            <small class="danger">{{ $message }}</small>
                        </div>
                    @enderror

                    @error('profile_image')
                        <div class="ml-36 md:ml-48">
                            <small class="danger">{{ $message }}</small>
                        </div>
                    @enderror
                </div>

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
                                    <small
                                        class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-']) }}</small>
                                    @error('zip_code')
                                        <small class="danger">{{ $message }}</small>
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
@endsection

@pushOnce('scripts')
    <script type="text/javascript">
        const pictureState = {
            isProfilePreviewMode: false,
            isCoverPreviewMode: false,
            init() {
                if ("{{ $data->cover_picture }}" != "") {
                    this.isCoverPreviewMode = true;
                }

                if ("{{ $data->profile_picture }}" != "") {
                    this.isProfilePreviewMode = true;
                }
            },
            showCoverPreview(event, targetID) {
                if (event.target.files.length <= 0) return;
                let src = URL.createObjectURL(event.target.files[0]);
                let preview = document.getElementById(targetID);
                this.isCoverPreviewMode = true;
                preview.src = src;
                preview.style.display = "block";
            },
            showProfilePreview(event, targetID) {
                if (event.target.files.length <= 0) return;
                let src = URL.createObjectURL(event.target.files[0]);
                let preview = document.getElementById(targetID);
                this.isProfilePreviewMode = true;
                preview.src = src;
                preview.style.display = "block";
            },
            clearCover(selfElem, inputID, previewID) {
                selfElem.preventDefault();
                document.getElementById(inputID).value = '';
                let preview = document.getElementById(previewID);
                this.isCoverPreviewMode = false;
                preview.src = "";
            }
        };
    </script>
@endPushOnce
