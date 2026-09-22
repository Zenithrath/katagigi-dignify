<x-app-layout>
    <x-slot:title>{{ __('patient.master.index.title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('patient.master.index.title') }}</h1>
                <p>{{ __('patient.master.index.subtitle') }}</p>
            </div>

            @can('create patient')
                <a href="{{ route('patients.create') }}" class="clickable-primary py-2 px-4 rounded-xl">
                    {{ __('patient.master.index.buttons.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        {{-- Tab kelengkapan data: Lengkap (siap SATUSEHAT) vs Belum Lengkap --}}
        @php
            $tab = request('tab', 'all');
            $tabUrl = fn ($value) => route('patients.index', array_filter([
                'tab' => $value !== 'all' ? $value : null,
                'keyword' => request('keyword') ?: null,
            ]));
        @endphp
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <a href="{{ $tabUrl('all') }}"
                class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ $tab === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ __('patient.master.index.table.all') }}
            </a>
            <a href="{{ $tabUrl('complete') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ $tab === 'complete' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <span class="w-2 h-2 rounded-full {{ $tab === 'complete' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                {{ __('patient.master.index.table.complete') }}
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-md {{ $tab === 'complete' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">{{ $completeness['complete'] }}</span>
            </a>
            <a href="{{ $tabUrl('incomplete') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ $tab === 'incomplete' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <span class="w-2 h-2 rounded-full {{ $tab === 'incomplete' ? 'bg-white' : 'bg-amber-500' }}"></span>
                {{ __('patient.master.index.table.incomplete') }}
                <span class="text-xs font-bold px-1.5 py-0.5 rounded-md {{ $tab === 'incomplete' ? 'bg-white/25 text-white' : 'bg-amber-50 text-amber-700' }}">{{ $completeness['incomplete'] }}</span>
            </a>
        </div>
        @if ($tab === 'incomplete')
            <p class="text-xs text-slate-500 mb-1">
                {{ __('patient.master.index.table.completeness_hint') }}
            </p>
        @endif

        <div class="content-card">
            <form action="{{ route('patients.index') }}" method="GET" class="flex items-end gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}" />
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-600 mb-1.5 block">{{ __('form.labels.patient_keyword') }}</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('form.placeholders.keyword') }}" class="custom-input" />
                </div>
                <button class="clickable-primary py-2.5 px-5 rounded-xl text-sm font-bold" type="submit">
                    {{ __('patient.record.index.actions.find') }}
                </button>
            </form>
        </div>

        <div class="flex items-center gap-2 mt-1 mb-2">
            <span class="text-sm text-slate-500">Menampilkan <span class="font-bold text-slate-700">{{ $patientList->total() }}</span> data pasien</span>
        </div>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('patient.master.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('patient.master.index.table.mr') }}</th>
                        <th scope="col" class="column">{{ __('patient.master.index.table.address') }}</th>
                        <th scope="col" class="column">{{ __('patient.master.index.table.completeness') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($patientList) > 0)
                        @foreach ($patientList as $index => $patient)
                            @php
                                $missing = \App\Services\Patient\MasterService::missingFields($patient);
                            @endphp
                            <tr>
                                <td class="column">
                                    {{ ($patientList->currentPage()-1) * $patientList->perPage() + ++$index }}
                                </td>
                                <td>
                                    <div class="flex items-center gap-3.5">
                                        @if ($patient->picture)
                                            <img src="{{ asset('storage/' . $patient->picture) }}" alt="{{ $patient->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-100 shrink-0" />
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center font-bold text-emerald-700 text-sm border-2 border-slate-100 shrink-0">{{ strtoupper(substr($patient->name, 0, 1)) }}</div>
                                        @endif
                                        <div class="flex flex-col min-w-0">
                                            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="font-bold text-slate-900 text-sm truncate hover:text-brand-600">{{ $patient->name }}</a>
                                            <span class="text-xs text-slate-400 truncate">{{ $patient->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">
                                    <span>{{ $patient->code }}</span>
                                </td>
                                <td class="column">
                                    <span>{{ implode(', ', array_filter([$patient->village, $patient->district, $patient->regency], fn($value) => !is_null($value) && $value !== '')) }}</span>
                                </td>
                                <td class="column">
                                    @if (empty($missing))
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('patient.master.index.table.complete') }}
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($missing as $field)
                                                <span class="px-1.5 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">{{ $field }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        <a href="{{ route('patients.edit', ['patient' => $patient->id]) }}" class="h-full">
                                            {{ __('patient.master.index.buttons.edit') }}
                                            <span class="sr-only">{{ $patient->name }}</span>
                                        </a>
                                        @can('delete patient')
                                            <form action="{{ route('patients.destroy', ['patient' => $patient->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.master.index.buttons.delete') }}<span
                                                        class="sr-only">{{ $patient->name }}</span></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.master.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>

        <x-table-paginator :paginator="$patientList" />
    </main>
</x-app-layout>
