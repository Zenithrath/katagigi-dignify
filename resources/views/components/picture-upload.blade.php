@props(['data', 'type' => 'create'])
<div class="picture-container" x-data="pictureState({{ ($data->cover_picture ?? null) ? 'true' : 'false' }}, {{ ($data->profile_picture ?? null) ? 'true' : 'false' }})">
    <div class="cover-picture">
        <img id="cover-preview" x-show="isCoverPreviewMode"
            src="{{ $type == 'update' && isset($data->cover_picture) ? url('storage/' . $data->cover_picture) : '' }}"
            alt="" srcset="" />
        <div class="picture-action-container">
            <label class="picture-action browse cursor-pointer">
                <input type="file" name="cover_image" id="cover_image"
                    @change="showCoverPreview(event, 'cover-preview')" accept="image/*" />
                <x-lucide-camera class="w-5 h-5" />
            </label>
            <button type="button" class="picture-action" @click="clearCover(event, 'cover_image', 'cover-preview')">
                <x-lucide-x class="w-5 h-5" />
            </button>
        </div>
    </div>

    <div class="profile-picture">
        <div class="relative w-full h-full">
            <img id="profile-preview" x-show="isProfilePreviewMode"
                src="{{ $type == 'update' && isset($data->profile_picture) ? url('storage/' . $data->profile_picture) : '' }}"
                alt="" srcset="" class="absolute w-full h-full object-cover" />
            <label class="absolute inset-0 flex items-center justify-center cursor-pointer z-10">
                <input type="file" name="profile_image" id="profile"
                    @change="showProfilePreview(event, 'profile-preview')" accept="image/*" />
                <div class="picture-action browse">
                    <x-lucide-camera class="w-5 h-5" />
                </div>
            </label>
        </div>
    </div>

    @error('cover_image')
        <div class="ml-36 md:ml-48 mt-1">
            <small class="danger">{{ $message }}</small>
        </div>
    @enderror

    @error('profile_image')
        <div class="ml-36 md:ml-48 mt-1">
            <small class="danger">{{ $message }}</small>
        </div>
    @enderror
</div>
