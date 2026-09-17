<x-app-layout>
    <x-slot:title>{{ 'Dashboard' }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('master.nurse.index.title') }}</h1>
                <p>{{ __('master.nurse.index.subtitle') }}</p>
            </div>

            @can('create nurse')
                <a href="{{ route('nurses.create') }}" class="clickable-primary py-2 px-4 rounded-xl">
                    {{ __('master.nurse.index.buttons.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('master.nurse.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('master.nurse.index.table.nipp') }}</th>
                        <th scope="col" class="column">{{ __('master.nurse.index.table.status') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($nurseList) > 0)
                        @foreach ($nurseList as $nurse)
                            <tr>
                                <td class="column">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="flex items-center gap-3.5">
                                        @if ($nurse->profile_picture)
                                            <img src="{{ asset('storage/' . $nurse->profile_picture) }}" alt="{{ $nurse->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-100 shrink-0" />
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center font-bold text-emerald-700 text-sm border-2 border-slate-100 shrink-0">{{ strtoupper(substr($nurse->name, 0, 1)) }}</div>
                                        @endif
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-slate-900 text-sm truncate">{{ $nurse->name }}</span>
                                            <span class="text-xs text-slate-400 truncate">{{ $nurse->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="column">{{ $nurse->nipp ?? '-' }}</td>
                                <td class="column">
                                    @if ($nurse->is_active)
                                        <span class="badge badge-success"><span class="dot"></span> {{ __('master.nurse.index.table.active') }}</span>
                                    @else
                                        <span class="badge badge-warning"><span class="dot"></span> {{ __('master.nurse.index.table.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">

                                        @can('update nurse')
                                            <a href="{{ route('nurses.edit', ['nurse' => $nurse->id]) }}" class="h-full">
                                                {{ __('master.nurse.index.buttons.edit') }}
                                                <span class="sr-only">{{ $nurse->name }}</span>
                                            </a>
                                        @endcan
                                        @if ($nurse->id !== auth()->user()->id)
                                            @can('delete nurse')
                                                <form action="{{ route('nurses.destroy', ['nurse' => $nurse->id]) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                        type="submit">{{ __('master.nurse.index.buttons.delete') }}<span
                                                            class="sr-only">{{ $nurse->name }}</span></button>
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
                                    {{ __('master.nurse.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>
    </main>
</x-app-layout>
