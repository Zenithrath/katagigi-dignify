<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Onboarding SATUSEHAT') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            @unless($enabled)
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                    SATUSEHAT belum dikonfigurasi — isi client_id/client_secret sandbox di config, lalu muat ulang.
                </div>
            @endunless

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h3 class="mb-3 font-semibold text-slate-800 dark:text-slate-100">1. Organization (klinik / cabang)</h3>
                <form method="POST" action="{{ route('satusehat.onboarding.organization') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Cabang</span>
                        <select name="branch_id" class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Nama Organization</span>
                        <input name="name" required maxlength="255"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Kode Nakes Facility</span>
                        <input name="code" maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Kode Wilayah (Kemendagri)</span>
                        <input name="region_code" maxlength="10" placeholder="mis. 3171000"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Telepon</span>
                        <input name="phone" maxlength="32"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Email</span>
                        <input name="email" type="email" maxlength="255"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Alamat</span>
                        <textarea name="address" rows="2" maxlength="2000"
                                  class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800"></textarea>
                    </label>
                    <button type="submit" class="btn-primary sm:col-span-2" @disabled(!$enabled)">
                        Daftarkan Organization
                    </button>
                </form>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h3 class="mb-3 font-semibold text-slate-800 dark:text-slate-100">2. Practitioner (dokter)</h3>
                <form method="POST" action="{{ route('satusehat.onboarding.practitioner') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Nama lengkap</span>
                        <input name="name" required maxlength="255"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">NIK (16 digit)</span>
                        <input name="nik" required pattern="\d{16}" maxlength="16"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Jenis kelamin</span>
                        <select name="gender" class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" required>
                            <option value="MALE">Laki-laki</option>
                            <option value="FEMALE">Perempuan</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Tanggal lahir</span>
                        <input name="birthdate" type="date" required
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">No. SIP</span>
                        <input name="sip_number" maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Telepon</span>
                        <input name="phone" maxlength="32"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <button type="submit" class="btn-primary sm:col-span-2" @disabled(!$enabled)">
                        Daftarkan Practitioner
                    </button>
                </form>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h3 class="mb-3 font-semibold text-slate-800 dark:text-slate-100">3. Location</h3>
                <form method="POST" action="{{ route('satusehat.onboarding.location') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Organization IHS</span>
                        <input name="organization_ihs" required maxlength="64"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Nama Location</span>
                        <input name="name" required maxlength="255"
                               class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Alamat</span>
                        <textarea name="address" rows="2" maxlength="2000"
                                  class="mt-1 w-full rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800"></textarea>
                    </label>
                    <button type="submit" class="btn-primary sm:col-span-2" @disabled(!$enabled)">
                        Daftarkan Location
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
