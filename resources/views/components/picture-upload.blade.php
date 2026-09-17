@props(['data', 'type' => 'create'])
<div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="pictureState({{ ($data->cover_picture ?? null) ? 'true' : 'false' }}, {{ ($data->profile_picture ?? null) ? 'true' : 'false' }})">
    {{-- Cover Photo --}}
    <div class="input-group">
        <label class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
            <x-lucide-image class="w-3.5 h-3.5" />
            Cover Photo
        </label>
        <div class="relative group">
            <div class="w-full h-36 md:h-40 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 overflow-hidden flex items-center justify-center transition-all group-hover:border-emerald-400 group-hover:bg-emerald-50/30">
                {{-- Preview image --}}
                <img id="cover-preview" x-show="isCoverPreviewMode"
                    src="{{ $type == 'update' && isset($data->cover_picture) ? url('storage/' . $data->cover_picture) : '' }}"
                    class="w-full h-full object-cover" />
                {{-- Placeholder --}}
                <div x-show="!isCoverPreviewMode" class="text-center">
                    <x-lucide-upload class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                    <p class="text-xs text-slate-400 font-medium">Click to upload cover photo</p>
                    <p class="text-[10px] text-slate-300 mt-0.5">JPG, PNG, max 2MB</p>
                </div>
            </div>
            {{-- Actions --}}
            <div class="absolute bottom-2 right-2 flex gap-1.5 z-10" x-show="isCoverPreviewMode">
                <label class="picture-action browse cursor-pointer">
                    <input type="file" name="cover_image" id="cover_image"
                        @change="showCoverPreview(event, 'cover-preview')" accept="image/*" />
                    <x-lucide-pencil class="w-3.5 h-3.5" />
                </label>
                <button type="button" class="picture-action !bg-red-50 !border-red-200 !text-red-500 hover:!bg-red-100"
                    @click="clearCover(event, 'cover_image', 'cover-preview')">
                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                </button>
            </div>
            {{-- Clickable overlay for empty state --}}
            <label x-show="!isCoverPreviewMode" class="absolute inset-0 cursor-pointer"></label>
            <input type="file" name="cover_image" class="picture-cover-input" accept="image/*"
                @change="showCoverPreview(event, 'cover-preview')" x-show="!isCoverPreviewMode" />
        </div>
        @error('cover_image')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Profile Photo --}}
    <div class="input-group">
        <label class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
            <x-lucide-user class="w-3.5 h-3.5" />
            Profile Photo
        </label>
        <div class="flex items-center gap-4">
            {{-- Avatar preview --}}
            <div class="relative w-20 h-20 md:w-24 md:h-24 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 overflow-hidden flex-shrink-0 group hover:border-emerald-400 hover:bg-emerald-50/30 transition-all">
                <img id="profile-preview" x-show="isProfilePreviewMode"
                    src="{{ $type == 'update' && isset($data->profile_picture) ? url('storage/' . $data->profile_picture) : '' }}"
                    class="w-full h-full object-cover" />
                <div x-show="!isProfilePreviewMode" class="w-full h-full flex flex-col items-center justify-center">
                    <x-lucide-camera class="w-6 h-6 text-slate-300" />
                    <p class="text-[10px] text-slate-300 mt-0.5">Photo</p>
                </div>
                {{-- Actions --}}
                <div class="absolute bottom-1 right-1 flex gap-1 z-10" x-show="isProfilePreviewMode">
                    <label class="w-6 h-6 rounded-md bg-white/90 border border-slate-200 flex items-center justify-center cursor-pointer hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all shadow-sm">
                        <input type="file" name="profile_image" id="profile"
                            @change="showProfilePreview(event, 'profile-preview')" accept="image/*" class="hidden" />
                        <x-lucide-pencil class="w-3 h-3" />
                    </label>
                </div>
                <label x-show="!isProfilePreviewMode" class="absolute inset-0 cursor-pointer"></label>
                <input type="file" name="profile_image" class="picture-profile-input hidden" accept="image/*"
                    @change="showProfilePreview(event, 'profile-preview')" />
            </div>
            {{-- Info --}}
            <div class="text-xs text-slate-400">
                <p class="font-medium text-slate-600">Profile Picture</p>
                <p class="mt-0.5">Square image, min 200x200px</p>
                <p>JPG or PNG, max 1MB</p>
            </div>
        </div>
        @error('profile_image')
            <small class="danger">{{ $message }}</small>
        @enderror
    </div>
</div>
