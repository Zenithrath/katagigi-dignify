<x-app-layout>
    <x-slot:title>{{ __('patient.master.index.title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('patient.master.index.title') }}</h1>
                <p>{{ __('patient.master.index.subtitle') }}</p>
            </div>

            @can('create patient')
                <a href="{{ route('patients.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('patient.master.index.buttons.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        <section class="bg-slate-50 p-8 rounded-md">
            <form action="{{ route('patients.index') }}" method="GET">
                <div class="input-group">
                    <label for="keyword">{{ __('form.labels.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1" value="{{ request('keyword') }}"
                            placeholder="{{ __('form.placeholders.keyword') }}" />
                        <button class="clickable-primary py-2 px-4 rounded-md" type="submit">
                            {{ __('patient.record.index.actions.find') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span>Found: {{ $patientList->total() }} entries.</span>
        </section>

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
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    <div class="flex gap-4 items-center">
                                        @if ($patient->picture)
                                            <img src="{{ asset('storage/' . $patient->picture) }}"
                                                alt="{{ $patient->name }}'s Picture"
                                                class="w-12 h-12 object-cover object-center rounded-full" />
                                        @else
                                            <div
                                                class="w-12 h-12 fill-none stroke-1 stroke-slate-900
                                                <x-lucide-user-circle class="w-full h-full" />
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-semibold">
                                                <a href="{{ route('patients.show', ['patient' => $patient->id]) }}"
                                                    class="h-full">
                                                    <span>{{ $patient->name }}</span>
                                                </a>
                                            </span>
                                            <span class="text-gray-500">{{ $patient->email }}</span>
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
