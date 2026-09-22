<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Profil Organization / Location') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h3 class="mb-3 font-semibold text-slate-800 dark:text-slate-100">Tambah / perbarui per cabang</h3>
                <form method="POST" action="{{ route('satusehat.org-profile.store') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Cabang</span>
                        <select name="branch_id" required
                                class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800">
                            @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Nama Organization</span>
                        <input name="organization_name" required maxlength="255"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Organization IHS</span>
                        <input name="organization_ihs" maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Location IHS</span>
                        <input name="location_ihs" maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Practitioner IHS</span>
                        <input name="practitioner_ihs" maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Kode Wilayah</span>
                        <input name="region_code" maxlength="10"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Alamat</span>
                        <textarea name="address" rows="2" maxlength="2000"
                                  class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800"></textarea>
                    </label>
                    <button type="submit" class="btn-primary sm:col-span-2">Simpan profil</button>
                </form>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-2">Cabang</th>
                            <th class="px-4 py-2">Organization</th>
                            <th class="px-4 py-2">IHS</th>
                            <th class="px-4 py-2">Location IHS</th>
                            <th class="px-4 py-2">Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-2">{{ $profile->branch?->name }}</td>
                                <td class="px-4 py-2">{{ $profile->organization_name }}</td>
                                <td class="px-4 py-2 font-mono text-xs">{{ $profile->organization_ihs ?? '—' }}</td>
                                <td class="px-4 py-2 font-mono text-xs">{{ $profile->location_ihs ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $profile->active ? 'Ya' : 'Tidak' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada profil.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
