<x-app-layout>
    <x-slot:title>{{ 'Dashboard' }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('master.nurse.index.title') }}</h1>
                <p>{{ __('master.nurse.index.subtitle') }}</p>
            </div>

            @can('create nurse')
                <a href="{{ route('nurses.create') }}" class="clickable-primary py-2 px-4 rounded-md">
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
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    @if ($nurse->profile_picture)
                                        <img src="{{ asset('storage/' . $nurse->profile_picture) }}"
                                            alt="{{ $nurse->name . "'s Profile Picture" }}"
                                            class="basis-12 h-12 object-cover object-center rounded-full" />
                                    @else
                                        <div
                                            class="basis-12 h-12 fill-none stroke-1 stroke-slate-900
                                            <x-lucide-user-circle class="w-full h-full" />
                                        </div>
                                    @endif
                                    <div class="flex flex-col w-64">
                                        <span class="font-semibold w-full truncate">{{ $nurse->name }}</span>
                                        <span class="text-gray-500 w-full truncate">{{ $nurse->email }}</span>
                                    </div>
                                </td>
                                <td class="column">{{ $nurse->nipp ?? '-' }}</td>
                                <td class="column">
                                    {{ $nurse->is_active ? __('master.nurse.index.table.active') : __('master.nurse.index.table.inactive') }}
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
