<x-app-layout>
    <x-slot:title>{{ 'Dashboard' }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('master.doctor.index.title') }}</h1>
                <p>{{ __('master.doctor.index.subtitle') }}</p>
            </div>

            @can('create doctor')
                <a href="{{ route('doctors.create') }}" class="clickable-primary py-2 px-4 rounded-xl">
                    {{ __('master.doctor.index.buttons.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('master.doctor.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('master.doctor.index.table.nipp') }}</th>
                        <th scope="col" class="column">{{ __('master.doctor.index.table.status') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($doctorList) > 0)
                        @foreach ($doctorList as $doctor)
                            <tr>
                                <td class="column">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="flex items-center gap-3.5">
                                        @if ($doctor->profile_picture)
                                            <img src="{{ asset('storage/' . $doctor->profile_picture) }}" alt="{{ $doctor->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-100 shrink-0" />
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center font-bold text-emerald-700 text-sm border-2 border-slate-100 shrink-0">{{ strtoupper(substr($doctor->name, 0, 1)) }}</div>
                                        @endif
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-slate-900 text-sm truncate">{{ $doctor->name }}</span>
                                            <span class="text-xs text-slate-400 truncate">{{ $doctor->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">{{ $doctor->nipp ?? '-' }}</td>
                                <td class="column">
                                    @if ($doctor->is_active)
                                        <span class="badge badge-success"><span class="dot"></span> {{ __('master.doctor.index.table.active') }}</span>
                                    @else
                                        <span class="badge badge-warning"><span class="dot"></span> {{ __('master.doctor.index.table.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">

                                        @can('update doctor')
                                            <a href="{{ route('doctors.edit', ['doctor' => $doctor->id]) }}" class="h-full">
                                                {{ __('master.doctor.index.buttons.edit') }}
                                                <span class="sr-only">{{ $doctor->name }}</span>
                                            </a>
                                        @endcan
                                        @if ($doctor->id !== auth()->user()->id)
                                            @can('delete doctor')
                                                <form action="{{ route('doctors.destroy', ['doctor' => $doctor->id]) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                        type="submit">{{ __('master.doctor.index.buttons.delete') }}<span
                                                            class="sr-only">{{ $doctor->name }}</span></button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('master.doctor.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>
    </main>
</x-app-layout>
