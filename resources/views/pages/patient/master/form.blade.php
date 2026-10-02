<x-app-layout>
    <x-slot:title>{{ $type == 'update' ? __('form.title.update.patient') : __('form.title.create.patient') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <x-back-button href="{{ $type == 'update' ? route('patients.show', $data->id) : route('patients.index') }}" />
                <h1>{{ $type == 'update' ? __('patient.master.form.title.edit') : __('patient.master.form.title.add') }}</h1>
                <p>{{ $type == 'update' ? __('patient.master.form.subtitle.update') : __('patient.master.form.subtitle.create') }}</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" enctype="multipart/form-data" action="{{ $action }}">
            @csrf

            @if ($type == 'update')
                @method('put')
            @endif

            <div class="content-card p-0 overflow-hidden">
                <x-picture-upload :data="$data" type="{{ $type }}" />

                <div class="p-8 flex flex-col">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                        <div class="input-group">
                            <label for="name">{{ __('form.labels.name') }}</label>
                            <input type="text" name="name" id="name" class="custom-input"
                                placeholder="{{ __('form.placeholders.name') }}" value="{{ $data->name ?? '' }}" required />
                            <small class="helper">{{ __('form.helpers.english_alpha_min', ['minlength' => 5]) }}</small>
                            @error('name')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="email">{{ __('form.labels.email') }}</label>
                            <input type="text" name="email" id="email" class="custom-input"
                                placeholder="{{ __('form.placeholders.email') }}" value="{{ $data->email ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            @error('email')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="payment_email">{{ __('form.labels.payment_email') }}</label>
                            <div class="relative flex">
                                <input type="text" name="payment_email" id="payment_email" class="custom-input flex-1 !pr-20"
                                    placeholder="{{ __('form.placeholders.payment_email') }}"
                                    value="{{ $data->payment_email ?? '' }}" />
                                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-emerald-600 hover:text-emerald-700"
                                    @click.prevent="copyEmail()">
                                    {{ __('form.actions.use_email') }}
                                </button>
                            </div>
                            <small class="helper">{{ __('form.helpers.valid_email') }}</small>
                            @error('payment_email')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="phone">{{ __('form.labels.phone') }}</label>
                            <div class="relative">
                                <div class="absolute left-0 flex items-center px-4 h-11 text-sm font-medium text-slate-500 border-r border-slate-200 rounded-l-xl bg-slate-50">+62</div>
                                <input type="tel" name="phone" id="phone" class="custom-input !pl-14"
                                    placeholder="{{ __('form.placeholders.phone') }}"
                                    value="{{ preg_replace('/^62/', '', $data->phone) ?? '' }}" />
                            </div>
                            <small class="helper">{{ __('form.helpers.phone') }}</small>
                            @error('phone')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="birthdate">{{ __('form.labels.birthdate') }}</label>
                            <input type="date" name="birthdate" id="birthdate" class="custom-input"
                                value="{{ $data->birthdate ?? '' }}" />
                            @error('birthdate')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="birth_place">{{ __('patient.master.form.labels.birth_place') }}</label>
                            <input type="text" name="birth_place" id="birth_place" class="custom-input"
                                placeholder="{{ __('patient.master.form.placeholders.birth_place') }}" value="{{ $data->birth_place ?? '' }}" />
                            @error('birth_place')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="nik">{{ __('patient.master.form.labels.nik') }}</label>
                            <input type="text" name="nik" id="nik" class="custom-input" inputmode="numeric"
                                placeholder="{{ __('patient.master.form.placeholders.nik') }}" value="{{ $data->nik ?? '' }}" />
                            <small class="helper">{{ __('patient.master.form.helpers.satusehat_nik') }}</small>
                            @error('nik')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="ihs_id">{{ __('patient.master.form.labels.ihs_id') }}</label>
                            <input type="text" name="ihs_id" id="ihs_id" class="custom-input"
                                placeholder="{{ __('patient.master.form.placeholders.ihs_id') }}" value="{{ $data->ihs_id ?? '' }}" />
                            @error('ihs_id')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="satusehat_consent" id="satusehat_consent" value="1"
                                    class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    {{ !empty($data->satusehat_consent) ? 'checked' : '' }} />
                                <span class="text-sm font-medium text-slate-700">{{ __('patient.master.form.labels.satusehat_consent') }}</span>
                            </label>
                            @error('satusehat_consent')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Fase 4.3: penjamin saat pendaftaran --}}
                        <div class="input-group">
                            <label for="insurance_id">Penjamin</label>
                            <select id="insurance_id" name="insurance_id" class="custom-select">
                                @foreach (\App\Models\MasterInsurance::orderBy('name')->get() as $ins)
                                    <option value="{{ $ins->id }}" @selected(old('insurance_id', $data->insurance_id ?? '') == $ins->id)>{{ $ins->name }}</option>
                                @endforeach
                            </select>
                            @error('insurance_id')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="insurance_number">No. kartu penjamin</label>
                            <input type="text" name="insurance_number" id="insurance_number" class="custom-input"
                                placeholder="No. BPJS / kartu asuransi" value="{{ old('insurance_number', $data->insurance_number ?? '') }}" />
                            @error('insurance_number')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="religion">{{ __('form.labels.religion') }}</label>
                            <select id="religion" name="religion" autocomplete="religion" class="custom-select">
                                <option {{ !isset($data->religion) || !$data->religion ? 'selected' : '' }} disabled>
                                    {{ __('form.placeholders.religion') }}
                                </option>
                                <option value="ISLAM" {{ $data->religion === 'ISLAM' ? 'selected' : '' }}>
                                    {{ __('form.labels.islam') }}
                                </option>
                                <option value="CHRISTIANITY" {{ $data->religion === 'CHRISTIANITY' ? 'selected' : '' }}>
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
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="marital_status">Status perkawinan</label>
                            <select id="marital_status" name="marital_status" autocomplete="off" class="custom-select">
                                <option value="" {{ empty($data->marital_status) ? 'selected' : '' }}>—</option>
                                <option value="S" @selected($data->marital_status === 'S')>Belum Menikah</option>
                                <option value="M" @selected($data->marital_status === 'M')>Menikah</option>
                                <option value="W" @selected($data->marital_status === 'W')>Cerai Hidup</option>
                                <option value="D" @selected($data->marital_status === 'D')>Cerai Mati</option>
                            </select>
                            @error('marital_status')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="gender">{{ __('form.labels.gender') }}</label>
                            <select id="gender" name="gender" autocomplete="gender-name" class="custom-select">
                                <option {{ !isset($data->gender) || !$data->gender || $data->gender === '' ? 'selected' : '' }} disabled>
                                    {{ __('form.placeholders.gender') }}
                                </option>
                                <option value="MALE" {{ $data->gender === 'MALE' ? 'selected' : '' }}>
                                    {{ __('form.labels.male') }}
                                </option>
                                <option value="FEMALE" {{ $data->gender === 'FEMALE' ? 'selected' : '' }}>
                                    {{ __('form.labels.female') }}
                                </option>
                            </select>
                            @error('gender')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="village">{{ __('form.labels.village') }}</label>
                            <input type="text" name="village" id="village" class="custom-input"
                                placeholder="{{ __('form.placeholders.village') }}"
                                value="{{ $data->village ?? '' }}"
                                list="regionVillageList" />
                            <datalist id="regionVillageList"></datalist>
                            <small class="helper">Pilih dari daftar wilayah Kemendagri bila tersedia; ketik manual juga bisa.</small>
                            @error('village')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1">
                        <div class="input-group md:col-span-1">
                            <label for="street">{{ __('form.labels.street') }}</label>
                            <input type="text" name="street" id="street" class="custom-input"
                                placeholder="{{ __('form.placeholders.street') }}"
                                value="{{ $data->street ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.alpha_or_marks', ['marks' => '.,-/']) }}</small>
                            @error('street')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="zip_code">{{ __('form.labels.zipcode') }}</label>
                            <input type="text" name="zip_code" id="zip_code" class="custom-input"
                                placeholder="{{ __('form.placeholders.zipcode') }}"
                                value="{{ $data->zip_code ?? '' }}" />
                            @error('zip_code')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="tonarigumi">{{ __('form.labels.tonarigumi') }}</label>
                            <input type="text" name="tonarigumi" id="tonarigumi" class="custom-input"
                                placeholder="{{ __('form.placeholders.tonarigumi') }}"
                                value="{{ $data->tonarigumi ?? '' }}" />
                            @error('tonarigumi')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1">
                        <div class="input-group">
                            <label for="district">{{ __('form.labels.district') }}</label>
                            <input type="text" name="district" id="district" class="custom-input"
                                placeholder="{{ __('form.placeholders.district') }}"
                                value="{{ $data->district ?? '' }}" />
                            <small class="helper">{{ __('form.helpers.alphanumeric') }}</small>
                            @error('district')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="regency">{{ __('form.labels.city') }}</label>
                            <input type="text" name="regency" id="regency" class="custom-input"
                                placeholder="{{ __('form.placeholders.city') }}" value="{{ $data->regency ?? '' }}"
                                list="regionCityList" />
                            <datalist id="regionCityList"></datalist>
                            <small class="helper">Pilih dari daftar wilayah Kemendagri bila tersedia; ketik manual juga bisa.</small>
                            @error('regency')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="input-group">
                            <label for="province">{{ __('form.labels.state') }}</label>
                            <input type="text" name="province" id="province" class="custom-input"
                                placeholder="{{ __('form.placeholders.state') }}"
                                value="{{ $data->province ?? '' }}"
                                list="regionProvinceList" />
                            <datalist id="regionProvinceList"></datalist>
                            <small class="helper">Pilih dari daftar wilayah Kemendagri bila tersedia; ketik manual juga bisa.</small>
                            @error('province')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- Kode wilayah Kemendagri (opsional; ikut terisi saat pilih dari daftar) --}}
                    <input type="hidden" name="region_code" id="region_code" value="{{ old('region_code', $data->region_code ?? '') }}" />

                    <div x-data="{ showSosmed: false }" class="mt-4 pt-6 border-t border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer select-none mb-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" x-model="showSosmed">
                            <span class="text-sm font-medium text-slate-700">{{ __('patient.master.form.labels.needed_sosmed') }}</span>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-1" x-show="showSosmed" x-transition>
                            <div class="input-group">
                                <label for="sosmed_fb">{{ __('form.labels.sosmed.facebook') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed_fb" class="custom-input"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->facebook ?? '' }}" />
                                @error('sosmed')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="sosmed_ig">{{ __('form.labels.sosmed.instagram') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed_ig" class="custom-input"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->instagram ?? '' }}" />
                                @error('sosmed')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="sosmed_tt">{{ __('form.labels.sosmed.tiktok') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed_tt" class="custom-input"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->tiktok ?? '' }}" />
                                @error('sosmed')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label for="sosmed_tw">{{ __('form.labels.sosmed.twitter') }}</label>
                                <input type="text" name="sosmed[]" id="sosmed_tw" class="custom-input"
                                    placeholder="{{ __('form.placeholders.sosmed') }}"
                                    value="{{ $data->sosmed->twitter ?? '' }}" />
                                @error('sosmed')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="btn-submit !w-auto !px-8">
                            {{ $type == 'update' ? __('form.actions.update') : __('form.actions.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

@pushOnce('scripts')
    <script type="text/javascript">
        function copyEmail() {
            let email = document.getElementById('email');
            let payment_email = document.getElementById('payment_email');

            payment_email.value = email.value;
        }

        // Fase 4.1: datalist wilayah Kemendagri (dropdown berantai lunak —
        // ketik manual tetap bisa). Memilih dari daftar mengisi kode wilayah.
        (function () {
            const route = @json(route('api.regions.lookup'));
            const fields = {
                province: { level: 'province', list: 'regionProvinceList' },
                regency: { level: 'city', list: 'regionCityList' },
                village: { level: 'village', list: 'regionVillageList' },
            };
            let lastFetched = {};

            function load(level, listId, parentCode, term) {
                const key = level + '|' + (parentCode || '') + '|' + (term || '');
                if (lastFetched[listId] === key) return;
                lastFetched[listId] = key;

                const params = new URLSearchParams({ level, q: term || '' });
                if (parentCode) params.set('parent_code', parentCode);
                fetch(route + '?' + params)
                    .then((r) => (r.ok ? r.json() : []))
                    .then((rows) => {
                        const dl = document.getElementById(listId);
                        if (!dl) return;
                        dl.innerHTML = '';
                        rows.forEach((row) => {
                            const opt = document.createElement('option');
                            opt.value = row.name;
                            opt.setAttribute('data-code', row.code);
                            dl.appendChild(opt);
                        });
                    });
            }

            function bind(fieldId, cfg) {
                const input = document.getElementById(fieldId);
                if (!input) return;
                const commit = () => {
                    const match = [...document.getElementById(cfg.list).options]
                        .find((o) => o.value === input.value);
                    const codeInput = document.getElementById('region_code');
                    if (match && codeInput) codeInput.value = match.getAttribute('data-code');
                };
                input.addEventListener('input', () => {
                    const parent = cfg.level === 'city' ? (document.getElementById('province').value || '') : '';
                    load(cfg.level, cfg.list, null, input.value);
                    commit();
                });
                input.addEventListener('change', commit);
            }

            Object.entries(fields).forEach(([fieldId, cfg]) => bind(fieldId, cfg));

            // Isi daftar awal (provinsi, kota Kalsel) saat halaman dibuka.
            load('province', 'regionProvinceList', null, '');
            load('city', 'regionCityList', null, '');
            load('village', 'regionVillageList', null, '');
        })();
    </script>
@endPushOnce
</x-app-layout>
