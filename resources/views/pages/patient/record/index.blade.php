<x-app-layout>
    <x-slot:title>{{ __('patient.record.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('patient.record.index._title') }}</h1>
                <p>{{ __('patient.record.index._subtitle') }}</p>
            </div>

            @can('create medical record')
                <a href="{{ route('medical-records.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('patient.record.index.actions.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        <section class="bg-slate-50 p-8 rounded-md">
            <form action="{{ route('medical-records.index') }}" method="GET">
                <div class="input-group">
                    <label for="keyword">{{ __('patient.record.index.table.patient_keyword') }}</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="keyword" id="keyword" class="flex-1" value="{{ request('keyword') }}"
                            placeholder="{{ __('patient.record.index.placeholders.patient_id') }}" />
                        <button class="clickable-primary py-2 px-4 rounded-md" type="submit">
                            {{ __('patient.record.index.actions.find') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="py-2 px-4 mt-4">
            <span>Found: {{ $medicalRecordList->total() }} entries.</span>
        </section>

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column w-72">{{ __('patient.record.index.table.patient') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.phone') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.service') }}</th>
                        <th scope="col" class="column w-56">{{ __('patient.record.index.table.doctor') }}</th>
                        <th scope="col" class="column">{{ __('patient.record.index.table.date') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($medicalRecordList) > 0)
                        @foreach ($medicalRecordList as $index => $record)
                            <tr>
                                <td class="column">
                                    {{ ($medicalRecordList->currentPage() - 1) * $medicalRecordList->perPage() + ++$index }}
                                </td>
                                <td class="column w-72">
                                    <div class="flex flex-col">
                                        <a href="{{ route('medical-records.show', ['medical_record' => $record->id]) }}"
                                            class="text-base mb-1">{{ $record->patient_name }}</a>
                                        <span>{{ $record->patient_code }}</span>
                                        <span class="w-72 truncate">{{ $record->patient_address }}</span>
                                    </div>
                                </td>
                                <td class="column">{{ $record->patient_phone }}</td>
                                <td class="column">
                                    <ul>
                                        @foreach (json_decode($record->services) as $item)
                                            <li>{{ $item->code }} - {{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="column w-56">{{ $record->doctor_name }}</td>
                                <td class="column">{{ $record->appointment_date }}</td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        @role('admin|doctor')
                                            <a href="{{ route('medical-records.edit', ['medical_record' => $record->id]) }}"
                                                class="h-full">
                                                {{ __('patient.record.index.actions.edit') }}
                                                <span class="sr-only">{{ $record->patient_name }}</span>
                                            </a>
                                        @endrole
                                        @role('admin|doctor')
                                            <form
                                                action="{{ route('medical-records.destroy', ['medical_record' => $record->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                    type="submit">{{ __('patient.record.index.actions.delete') }}<span
                                                        class="sr-only">{{ $record->patient_name }}</span></button>
                                            </form>
                                        @endrole
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('patient.record.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>

        <x-table-paginator :paginator="$medicalRecordList" />
    </main>
</x-app-layout>
