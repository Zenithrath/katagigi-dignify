<x-app-layout>
    <x-slot:title>{{ 'Dashboard' }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('master.admin.index.title') }}</h1>
                <p>{{ __('master.admin.index.subtitle') }}</p>
            </div>

            @can('create admin')
                <a href="{{ route('admins.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('master.admin.index.buttons.add') }}
                </a>
            @endcan
        </section>

        <x-flash-alerts />

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('master.admin.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('master.admin.index.table.nipp') }}</th>
                        <th scope="col" class="column">{{ __('master.admin.index.table.status') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($adminList) > 0)
                        @foreach ($adminList as $admin)
                            <tr>
                                <td class="column">{{ $loop->iteration }}</td>
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    @if ($admin->profile_picture)
                                        <img src="{{ asset('storage/' . $admin->profile_picture) }}"
                                            alt="{{ $admin->name . "'s Profile Picture" }}"
                                            class="basis-12 h-12 object-cover object-center rounded-full" />
                                    @else
                                        <div
                                            class="basis-12 h-12 fill-none stroke-1 stroke-slate-900
                                            <x-lucide-user-circle class="w-full h-full" />
                                        </div>
                                    @endif
                                    <div class="flex flex-col w-64">
                                        <span class="font-semibold w-full truncate">{{ $admin->name }}</span>
                                        <span class="text-gray-500 w-full truncate">{{ $admin->email }}</span>
                                    </div>
                                </td>
                                <td class="column">{{ $admin->nipp ?? '-' }}</td>
                                <td class="column">
                                    {{ $admin->is_active ? __('master.admin.index.table.active') : __('master.admin.index.table.inactive') }}
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">

                                        @can('update admin')
                                            <a href="{{ route('admins.edit', ['admin' => $admin->id]) }}" class="h-full">
                                                {{ __('master.admin.index.buttons.edit') }}
                                                <span class="sr-only">{{ $admin->name }}</span>
                                            </a>
                                        @endcan
                                        @if ($admin->id !== auth()->user()->id)
                                            @can('delete admin')
                                                <form action="{{ route('admins.destroy', ['admin' => $admin->id]) }}"
                                                    method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                        type="submit">{{ __('master.admin.index.buttons.delete') }}<span
                                                            class="sr-only">{{ $admin->name }}</span></button>
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
                                    {{ __('master.admin.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>
    </main>
</x-app-layout>
