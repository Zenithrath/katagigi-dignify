<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Kredensial SATUSEHAT per Cabang') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                Setiap cabang memakai klien OAuth SATUSEHAT sendiri (syarat multi-cabang).
                Client secret disimpan terenkripsi dan tidak pernah ditampilkan kembali —
                biarkan kosong bila tidak ingin mengubahnya. Tanpa kredensial cabang, sistem
                memakai kredensial global (.env) sebagai fallback.
            </div>

            @foreach ($branches as $branch)
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <h3 class="mb-3 font-semibold text-slate-800 dark:text-slate-100">{{ $branch->name }}</h3>
                    <form method="POST" action="{{ route('satusehat.credentials.update', $branch->id) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Client ID <span class="text-red-500">*</span></span>
                            <input name="client_id" required maxlength="191" value="{{ old('client_id', $branch->satusehatCredential?->client_id) }}"
                                   class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">
                                Client Secret @if ($branch->satusehatCredential) (terisi — kosongkan bila tidak diubah) @else <span class="text-red-500">*</span> @endif
                            </span>
                            <input name="client_secret" type="password" maxlength="191" placeholder="{{ $branch->satusehatCredential ? '••••••••' : '' }}"
                                   class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Organization ID (IHS)</span>
                            <input name="organization_id" maxlength="64" value="{{ old('organization_id', $branch->satusehatCredential?->organization_id) }}"
                                   class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Location ID (IHS)</span>
                            <input name="location_id" maxlength="64" value="{{ old('location_id', $branch->satusehatCredential?->location_id) }}"
                                   class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Lingkungan</span>
                            <select name="environment" class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800">
                                @foreach (\App\Models\SatuSehatCredential::ENVIRONMENTS as $code => $label)
                                    <option value="{{ $code }}" @selected(old('environment', $branch->satusehatCredential?->environment ?? 'sandbox') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Status</span>
                            <select name="is_active" class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800">
                                <option value="1" @selected(old('is_active', $branch->satusehatCredential?->is_active ?? true) == 1)>Aktif</option>
                                <option value="0" @selected(old('is_active', $branch->satusehatCredential?->is_active ?? true) == 0)>Nonaktif</option>
                            </select>
                        </label>
                        <div class="flex items-center gap-3 sm:col-span-2">
                            <button type="submit" class="btn-submit !w-auto !px-8">Simpan</button>
                            @if ($branch->satusehatCredential)
                                <a href="{{ route('satusehat.credentials.verify', $branch->id) }}"
                                   class="text-sm text-brand-600 hover:text-brand-700">Verifikasi token</a>
                            @endif
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
