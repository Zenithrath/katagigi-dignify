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

        <div class="content-card">
            <form action="{{ route('patients.index') }}" method="GET" class="flex items-end gap-3">
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
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($patientList) > 0)
                        @foreach ($patientList as $index => $patient)
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
