{{-- x-picture-upload: cover + profile picture upload widget
     Props: $data (model with cover_picture/profile_picture), $type (create|update) --}}
@props(['data', 'type' => 'create'])
<div class="picture-container" x-data="pictureState({{ ($data->cover_picture ?? null) ? 'true' : 'false' }}, {{ ($data->profile_picture ?? null) ? 'true' : 'false' }})">
    <div class="cover-picture overflow-hidden relative">
        <img id="cover-preview" x-show="isCoverPreviewMode"
            src="{{ $type == 'update' && isset($data->cover_picture) ? url('storage/' . $data->cover_picture) : '' }}"
            alt="" srcset="" />
        <div class="picture-action-container absolute">
            <label>
                <input type="file" name="cover_image" id="cover_image"
                    @change="showCoverPreview(event, 'cover-preview')" />
                <div class="picture-action browse">
                    <x-lucide-camera class="w-5 h-5" />
                </div>
            </label>
            <button class="picture-action" @click="clearCover(event, 'cover_image', 'cover-preview')">
                <x-lucide-x class="w-5 h-5" />
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
                        <x-lucide-camera class="w-5 h-5" />
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
