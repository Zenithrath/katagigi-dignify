<x-app-layout>
    <x-slot:title>{{ __('general.service.index._title') }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('general.service.index._title') }}</h1>
                <p>{{ __('general.service.index._subtitle') }}</p>
            </div>

            <div class="flex gap-2">
                @can('read category')
                    <a href="{{ route('categories.index') }}" class="clickable-primary py-2 px-4 rounded-xl">
                        {{ __('general.category.index._nav') }}
                    </a>
                @endcan

                @can('create service')
                    <a href="{{ route('services.create') }}" class="clickable-primary py-2 px-4 rounded-xl">
                        {{ __('general.service.index.action.add') }}
                    </a>
                @endcan
            </div>
        </section>

        <x-flash-alerts />

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('general.service.index.table.code') }}</th>
                        <th scope="col" class="column">{{ __('general.service.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('general.service.index.table.category') }}</th>
                        <th scope="col" class="column">{{ __('general.service.index.table.range_price') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($serviceList) > 0)
                        @foreach ($serviceList as $service)
                            <tr>
                                <td class="column">{{ $loop->iteration }}</td>
                                <td class="index-column w-64 truncate">
                                    {{ $service->code }}
                                </td>
                                <td class="column">
                                    <a href="{{ route('services.show', ['service' => $service->id]) }}">
                                        {{ $service->name }}
                                    </a>
                                </td>
                                <td class="column">
                                    {{ $service->category_name ?? __('general.service.index.table.uncategorized') }}
                                </td>
                                <td class="column">
                                    &plusmn; {{ $toRupiah($service->lower_price) }} &dash;
                                    {{ $toRupiah($service->upper_price) }}
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        {{-- @can('update service') --}}
                                        <a href="{{ route('services.edit', ['service' => $service->id]) }}" class="h-full rounded-xl">
                                            {{ __('general.service.index.action.edit') }}
                                            <span class="sr-only">{{ $service->name }}</span>
                                        </a>
                                        {{-- @endcan --}}
                                        @if ($service->id !== auth()->user()->id)
                                            {{-- @can('delete service') --}}
                                            <form action="{{ route('services.destroy', ['service' => $service->id]) }}"
                                                method="post">
                                                @csrf
                                                @method('delete')
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700 rounded-xl"
                                                    type="submit">{{ __('general.service.index.action.delete') }}<span
                                                        class="sr-only">{{ $service->name }}</span></button>
                                            </form>
                                            {{-- @endcan --}}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="column text-center" colspan="6">
                                <div class="h-24 w-full flex items-center justify-center">
                                    {{ __('general.service.index.table.empty') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>

        <section class="mt-4">
            <nav class="flex items-center justify-between border-t border-slate-200 px-1 py-3">
                <div class="flex w-0 flex-1">
                    @if ($pagination->page > 1)
                        <a href="{{ route('services.index', ['page' => $pagination->page - 1]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd"/></svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                        </a>
                    @endif
                </div>

                <div class="hidden md:flex items-center gap-1">
                    @if ($pagination->page - 3 >= 0)
                        <a href="{{ route('services.index', ['page' => 1]) }}"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">{{ 1 }}</a>
                    @endif

                    @if ($pagination->page - 3 > 0)
                        <span class="px-1 text-slate-400">...</span>
                    @endif

                    @foreach (range(1, $pagination->last) as $page)
                        @if ($page > $pagination->page - 2 && $page < $pagination->page + 2)
                            <a href="{{ route('services.index', ['page' => $page]) }}"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $page === $pagination->page ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($pagination->page + 2 < $pagination->last)
                        <span class="px-1 text-slate-400">...</span>
                    @endif

                    @if ($pagination->page + 2 <= $pagination->last)
                        <a href="{{ route('services.index', ['page' => $pagination->last]) }}"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">{{ $pagination->last }}</a>
                    @endif
                </div>

                <div class="flex w-0 flex-1 justify-end">
                    @if ($pagination->page < $pagination->last)
                        <a href="{{ route('services.index', ['page' => $pagination->page + 1]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                </div>
            </nav>
        </section>
    </main>
</x-app-layout>
