<x-app-layout>
    <x-slot:title>{{ __('general.schedule.index.title') }}</x-slot:title>

    <main class="main-table-container" x-data="scheduleLookup">
        <section class="heading">
            <div>
                <h1>{{ __('general.schedule.index.title') }}</h1>
                <p>{{ __('general.schedule.index.subtitle') }}</p>
            </div>
        </section>

        <x-flash-alerts />

        {{-- FORM INPUT --}}
        @role('admin|nurse')
            <div class="content-card">
                <div class="card-header-clean">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M10 14h1"/><path d="M14 14h1"/></svg>
                    </div>
                    <h2>{{ __('general.schedule.form.title._title') }}</h2>
                </div>

                <form method="post" action="{{ route('schedules.store') }}" x-data="{ processing: false, doctorOpen: false, doctorValue: '{{ old('doctor_id') }}', dayOpen: false, dayValue: '{{ old('day') }}' }" @submit="processing = true">
                    @csrf
                    <div class="form-grid">
                        {{-- Dokter --}}
                        <div class="input-wrapper col-4">
                            <label>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                {{ __('general.schedule.form.labels.doctor._title') }}
                            </label>
                            <input type="hidden" name="doctor_id" :value="doctorValue" required />
                            <div @click.away="doctorOpen = false" style="position: relative;">
                                <button type="button" @click="doctorOpen = !doctorOpen"
                                    class="dropdown-trigger"
                                    :class="doctorValue ? 'has-value' : 'is-empty'">
                                    <span x-text="doctorValue ? document.querySelector(`[data-doctor-val='${doctorValue}']`)?.textContent || '{{ __('general.schedule.form.labels.doctor.default') }}' : '{{ __('general.schedule.form.labels.doctor.default') }}'"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="doctorOpen ? 'transform: rotate(180deg)' : ''" style="transition: transform 0.2s; flex-shrink: 0; color: #94a3b8;"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="doctorOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    class="dropdown-panel">
                                    <div class="dropdown-item is-placeholder" x-text="'{{ __('general.schedule.form.labels.doctor.default') }}'"></div>
                                    @if (count($doctorList) == 0)
                                        <div class="dropdown-item is-empty-state">{{ __('general.schedule.form.labels.doctor.empty') }}</div>
                                    @else
                                        @foreach ($doctorList as $doctor)
                                            <div class="dropdown-item" :class="doctorValue === '{{ $doctor->id }}' ? 'is-selected' : ''"
                                                data-doctor-val="{{ $doctor->id }}"
                                                @click="doctorValue = '{{ $doctor->id }}'; doctorOpen = false">{{ $doctor->name }}</div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @error('doctor_id')
                                <small class="danger">{{ $message }}</small>
                            @enderror
                        </div>

                        @can('create schedule')
                            {{-- Hari --}}
                            <div class="input-wrapper col-3">
                                <label>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                    {{ __('general.schedule.form.labels.day._title') }}
                                </label>
                                <input type="hidden" name="day" :value="dayValue" required />
                                <div @click.away="dayOpen = false" style="position: relative;">
                                    <button type="button" @click="dayOpen = !dayOpen"
                                        class="dropdown-trigger"
                                        :class="dayValue ? 'has-value' : 'is-empty'">
                                        <span x-text="dayValue ? document.querySelector(`[data-day-val='${dayValue}']`)?.textContent || '{{ __('general.schedule.form.labels.day.default') }}' : '{{ __('general.schedule.form.labels.day.default') }}'"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="dayOpen ? 'transform: rotate(180deg)' : ''" style="transition: transform 0.2s; flex-shrink: 0; color: #94a3b8;"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                    <div x-show="dayOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="dropdown-panel">
                                        <div class="dropdown-item is-placeholder" x-text="'{{ __('general.schedule.form.labels.day.default') }}'"></div>
                                        @foreach (['MONDAY' => 'monday', 'TUESDAY' => 'tuesday', 'WEDNESDAY' => 'wednesday', 'THURSDAY' => 'thursday', 'FRIDAY' => 'friday', 'SATURDAY' => 'saturday', 'SUNDAY' => 'sunday'] as $value => $key)
                                            <div class="dropdown-item" :class="dayValue === '{{ $value }}' ? 'is-selected' : ''"
                                                data-day-val="{{ $value }}"
                                                @click="dayValue = '{{ $value }}'; dayOpen = false">{{ __('general.schedule.form.labels.day.' . $key) }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('day')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Waktu Mulai --}}
                            <div class="input-wrapper col-2">
                                <label for="start_time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ __('general.schedule.form.labels.start_time') }}
                                </label>
                                <input type="time" name="start_time" id="start_time" class="custom-input" required value="{{ old('start_time') }}" />
                                @error('start_time')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Waktu Selesai --}}
                            <div class="input-wrapper col-2">
                                <label for="end_time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ __('general.schedule.form.labels.end_time') }}
                                </label>
                                <input type="time" name="end_time" id="end_time" class="custom-input" required value="{{ old('end_time') }}" />
                                @error('end_time')
                                    <small class="danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Tombol Submit --}}
                            <div class="col-12">
                                <button type="submit" class="btn-submit" :disabled="processing">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                                    <span x-show="!processing">{{ __('general.schedule.form.buttons.add') }}</span>
                                    <span x-show="processing">Menambahkan...</span>
                                </button>
                            </div>
                        @endcan
                    </div>
                </form>
            </div>
        @endrole

        {{-- TABEL --}}
        @role('admin|nurse')
            <div class="content-card !p-0">
                {{-- Top Bar --}}
                <div class="flex items-center justify-between px-7 py-5 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-bold text-slate-900">Daftar Jadwal Bekerja</h2>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200/70" x-text="pagination.total + ' Jadwal'"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500">Dokter:</span>
                        <div x-data="{ open: false }" @click.away="open = false" class="relative" style="min-width: 180px;">
                            <button type="button" @click="open = !open"
                                class="dropdown-trigger !h-[34px] !text-[13px] !pl-3 !pr-8 !rounded-lg !border-slate-200 !bg-white"
                                :class="filterDoctorId ? 'has-value' : 'is-empty'">
                                <span x-text="filterDoctorId ? $el.parentElement.querySelector(`[data-value='${filterDoctorId}']`)?.textContent || 'Semua Dokter' : 'Semua Dokter'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform shrink-0"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                class="dropdown-panel w-full">
                                <div class="dropdown-item text-[13px] py-2 px-3.5" :class="filterDoctorId === '' ? 'is-selected' : ''"
                                    @click="filterDoctorId = ''; pagination.page = 1; lookup(); open = false">Semua Dokter</div>
                                @foreach ($doctorList as $doctor)
                                    <div class="dropdown-item text-[13px] py-2 px-3.5" :class="filterDoctorId === '{{ $doctor->id }}' ? 'is-selected' : ''"
                                        data-value="{{ $doctor->id }}"
                                        @click="filterDoctorId = '{{ $doctor->id }}'; pagination.page = 1; lookup(); open = false">{{ $doctor->name }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px;" scope="col" class="column">NO</th>
                                <th scope="col" class="index-column">NAMA DOKTER</th>
                                <th scope="col" class="column">HARI</th>
                                <th scope="col" class="column">JAM KERJA</th>
                                <th scope="col" class="column">STATUS</th>
                                <th scope="col" class="action-column">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="isLoading">
                                <tr><td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memuat data...
                                    </div>
                                </td></tr>
                            </template>
                            <template x-if="!isLoading && scheduleList.length === 0">
                                <tr><td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                        <span class="text-sm">{{ __('general.schedule.index.table.empty') }}</span>
                                    </div>
                                </td></tr>
                            </template>
                            <template x-if="!isLoading && scheduleList.length > 0">
                                <template x-for="(schedule, index) in scheduleList" :key="schedule.id">
                                    <tr>
                                        <td class="index-column font-semibold text-slate-500" x-text="String((pagination.page - 1) * pagination.limit + index + 1).padStart(2, '0')"></td>
                                        <td>
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center font-bold text-emerald-700 text-sm border-2 border-slate-100 shrink-0" x-text="schedule.name ? schedule.name.split(' ').filter(n => n.length > 0).map(n => n[0]).slice(0,2).join('').toUpperCase() : '?'"></div>
                                                <span class="font-bold text-slate-900 text-sm" x-text="schedule.name"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-[13px] font-semibold text-slate-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>
                                                <span x-text="ucfirst(schedule.day)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inline-flex items-center gap-1.5 font-semibold text-slate-800 text-[13px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                <span x-text="formatTime(schedule.time_start)"></span>
                                                <span class="text-slate-300">-</span>
                                                <span x-text="formatTime(schedule.time_end)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <template x-if="schedule.availability === 'AVAILABLE'">
                                                <span class="badge badge-success"><span class="dot"></span> Tersedia</span>
                                            </template>
                                            <template x-if="schedule.availability !== 'AVAILABLE'">
                                                <span class="badge badge-warning"><span class="dot"></span> Tidak Tersedia</span>
                                            </template>
                                        </td>
                                        <td class="action-column">
                                            <div class="flex items-center justify-end gap-1">
                                                @can('update schedule')
                                                    <form
                                                        :action="`{{ route('schedules.update_status', ['schedule' => '__ID__']) }}`.replace('__ID__', schedule.id)"
                                                        method="post"
                                                        @submit.prevent="if(confirm(schedule.availability === 'AVAILABLE' ? 'Tandai tidak tersedia?' : 'Tandai tersedia?')) $el.submit()">
                                                        @csrf
                                                        <button class="action-btn" type="submit" :title="schedule.availability === 'AVAILABLE' ? 'Tandai tidak tersedia' : 'Tandai tersedia'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                                        </button>
                                                    </form>
                                                @endcan

                                                @can('delete schedule')
                                                    <form
                                                        :action="`{{ route('schedules.destroy', ['schedule' => '__ID__']) }}`.replace('__ID__', schedule.id)"
                                                        method="post"
                                                        @submit.prevent="if(confirm('Hapus jadwal ini?')) $el.submit()">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="action-btn" type="submit" title="Hapus">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        @endrole

        {{-- Doctor table (read-only) --}}
        @role('doctor')
            <div class="content-card !p-0">
                <div class="flex items-center gap-3 px-7 py-5 border-b border-slate-200">
                    <h2 class="text-sm font-bold text-slate-900">Jadwal Saya</h2>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200/70" x-text="pagination.total + ' Jadwal'"></span>
                </div>

                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px;" scope="col" class="column">NO</th>
                                <th scope="col" class="index-column">HARI</th>
                                <th scope="col" class="column">JAM KERJA</th>
                                <th scope="col" class="column">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="isLoading">
                                <tr><td colspan="4" class="text-center py-12 text-slate-400">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memuat data...
                                    </div>
                                </td></tr>
                            </template>
                            <template x-if="!isLoading && scheduleList.length === 0">
                                <tr><td colspan="4" class="text-center py-12">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                        <span class="text-sm">{{ __('general.schedule.index.table.empty') }}</span>
                                    </div>
                                </td></tr>
                            </template>
                            <template x-if="!isLoading && scheduleList.length > 0">
                                <template x-for="(schedule, index) in scheduleList" :key="schedule.id">
                                    <tr>
                                        <td class="index-column font-semibold text-slate-500" x-text="String((pagination.page - 1) * pagination.limit + index + 1).padStart(2, '0')"></td>
                                        <td>
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-[13px] font-semibold text-slate-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>
                                                <span x-text="ucfirst(schedule.day)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inline-flex items-center gap-1.5 font-semibold text-slate-800 text-[13px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                <span x-text="formatTime(schedule.time_start)"></span>
                                                <span class="text-slate-300">-</span>
                                                <span x-text="formatTime(schedule.time_end)"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <template x-if="schedule.availability === 'AVAILABLE'">
                                                <span class="badge badge-success"><span class="dot"></span> Tersedia</span>
                                            </template>
                                            <template x-if="schedule.availability !== 'AVAILABLE'">
                                                <span class="badge badge-warning"><span class="dot"></span> Tidak Tersedia</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        @endrole

        {{-- Pagination --}}
        <section class="mt-4">
            <nav class="flex items-center justify-between bg-white rounded-2xl border border-slate-200 shadow-sm px-4 py-3">
                <div class="flex w-0 flex-1">
                    <template x-if="pagination.page > 1">
                        <button type="button" @click="handlePreviousPage()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                        </button>
                    </template>
                </div>

                <div class="hidden md:flex items-center gap-1.5">
                    <template x-if="pagination.page - 3 >= 0">
                        <button type="button" x-text="1"
                            class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200"
                            @click="pagination.page = 1; lookup()"></button>
                    </template>
                    <template x-if="pagination.page - 3 > 0">
                        <span class="px-1 text-slate-400">...</span>
                    </template>
                    <template x-for="page in pagination.last" :key="page">
                        <template x-if="page > (pagination.page - 2) && page < (pagination.page + 2)">
                            <button type="button" x-text="page"
                                :class="`${(pagination.page == page) ? 'min-w-[42px] h-10 flex items-center justify-center text-sm font-bold px-2 text-white cursor-pointer relative overflow-hidden' : 'w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200'}`"
                                :style="pagination.page == page ? 'background: radial-gradient(circle at 20% 20%, #165b38 0%, #0a331f 40%, #051d11 80%); box-shadow: inset 1.5px 2px 3px rgba(255,255,255,0.5), inset -2px -2.5px 4px rgba(0,0,0,0.8), 0 3px 8px rgba(5,29,17,0.45); border: 1px solid rgba(0,0,0,0.5); border-radius: 16px;' : ''"
                                @click="pagination.page = page; lookup()"></button>
                        </template>
                    </template>
                    <template x-if="pagination.page + 2 < pagination.last">
                        <span class="px-1 text-slate-400">...</span>
                    </template>
                    <template x-if="pagination.page + 2 <= pagination.last">
                        <button type="button" x-text="pagination.last"
                            class="w-10 h-10 flex items-center justify-center text-sm font-semibold rounded-2xl text-slate-600 border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200"
                            @click="pagination.page = pagination.last; lookup()"></button>
                    </template>
                </div>

                <div class="flex w-0 flex-1 justify-end">
                    <template x-if="pagination.page < pagination.last">
                        <button type="button" @click="handleNextPage()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 rounded-2xl border border-slate-200 bg-white shadow-sm hover:bg-white hover:text-slate-900 hover:shadow hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-200">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                        </button>
                    </template>
                </div>
            </nav>
        </section>
    </main>

@pushOnce('scripts')
    <script type="text/javascript">
        const scheduleLookup = {
            keyword: "",
            filterDoctorId: "",
            scheduleList: [],
            isLoading: false,
            pagination: {
                page: 1,
                limit: 20,
                last: 1,
                total: 1,
            },
            init() {
                this.lookup();
            },
            lookup() {
                this.isLoading = true;
                this.scheduleList = [];
                let params = new URLSearchParams({
                    keyword: this.keyword,
                    page: this.pagination.page,
                    limit: this.pagination.limit,
                });
                if (this.filterDoctorId) {
                    params.append('doctor_id', this.filterDoctorId);
                }
                @role('admin|nurse')
                    fetch(`{{ route('api.schedules.lookup') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.scheduleList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: Math.max(1, data.pagination.last),
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                        .catch(() => { this.isLoading = false; })
                @endrole
                @role('doctor')
                    fetch(`{{ route('api.schedules.lookup.doctor') }}?${params}`)
                        .then(response => response.json())
                        .then(data => {
                            this.scheduleList = data.data;
                            this.pagination = {
                                ...this.pagination,
                                last: Math.max(1, data.pagination.last),
                                total: data.pagination.total,
                            };
                            this.isLoading = false;
                        })
                        .catch(() => { this.isLoading = false; })
                @endrole
            },
            handleNextPage() {
                this.pagination.page++;
                this.lookup();
            },
            handlePreviousPage() {
                this.pagination.page--;
                this.lookup();
            },
            formatTime(timeString) {
                if (!timeString) return '-';
                const parts = timeString.split(':');
                return (parts[0] || '00').padStart(2, '0') + ':' + (parts[1] || '00').padStart(2, '0');
            },
            ucfirst(text) {
                if (!text) return '';
                const t = text.toLowerCase();
                return t.charAt(0).toUpperCase() + t.slice(1);
            },
        }
    </script>
@endPushOnce
</x-app-layout>
