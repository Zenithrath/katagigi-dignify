{{-- Upload foto profil saja (tanpa cover) — dipakai form master & profile.
     Style mengikuti input-group: avatar rounded-xl, dashed border, aksi edit/hapus
     muncul saat sudah ada preview. --}}
@props(['data', 'type' => 'create'])
<div class="input-group !mb-0 p-8 pb-2" x-data="pictureState({{ ($data->profile_picture ?? null) ? 'true' : 'false' }})">
    <div class="flex items-center gap-5">
        {{-- Avatar preview --}}
        <div class="relative w-24 h-24 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 overflow-hidden flex-shrink-0 group hover:border-emerald-400 hover:bg-emerald-50/30 transition-all">
            <img id="profile-preview" x-show="isProfilePreviewMode" x-cloak
                src="{{ $type == 'update' && isset($data->profile_picture) ? url('storage/' . $data->profile_picture) : '' }}"
                class="w-full h-full object-cover" />
            <div x-show="!isProfilePreviewMode" class="w-full h-full flex flex-col items-center justify-center">
                <x-lucide-camera class="w-7 h-7 text-slate-300" />
                <p class="text-[10px] text-slate-300 mt-1">Photo</p>
            </div>
        </div>

        {{-- Info + aksi --}}
        <div class="min-w-0">
            <p class="text-sm font-bold text-slate-900">Profile Picture</p>
            <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, atau GIF. Maks 1MB.</p>

            <div class="flex items-center gap-2 mt-3">
                <label class="picture-action cursor-pointer">
                    <input type="file" name="profile_image" id="profile"
                        @change="showProfilePreview(event, 'profile-preview')" accept="image/*" class="hidden" />
                    <x-lucide-upload class="w-3.5 h-3.5" />
                    <span>Upload</span>
                </label>

                <template x-if="isProfilePreviewMode">
                    <button type="button" class="picture-action !bg-red-50 !border-red-200 !text-red-500 hover:!bg-red-100"
                        @click="clearProfile(event, 'profile_image', 'profile-preview')">
                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                        <span>Remove</span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    @error('profile_image')
        <small class="danger">{{ $message }}</small>
    @enderror
</div>
