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
                    <a href="{{ route('categories.index') }}" class="clickable-primary py-2 px-4 rounded-md">
                        {{ __('general.category.index._nav') }}
                    </a>
                @endcan

                @can('create service')
                    <a href="{{ route('services.create') }}" class="clickable-primary py-2 px-4 rounded-md">
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
                                        <a href="{{ route('services.edit', ['service' => $service->id]) }}" class="h-full">
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
                                                <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
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
            <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                <div class="-mt-px flex w-0 flex-1">
                    @if ($pagination->page > 1)
                        <a href="{{ route('services.index', ['page' => $pagination->page - 1]) }}"
                            class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1l-2.1 1.95h12.59A.75.75 0 0118 10z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden md:block">{{ __('Previous') }}</span>
                            </button>
                    @endif
                </div>

                <div class="md:-mt-px flex">
                    @if ($pagination->page - 3 >= 0)
                        <a href="{{ route('services.index', ['page' => 1]) }}" @class([
                            'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700',
                        ])
                            aria-current="page">{{ 1 }}</a>
                    @endif

                    @if ($pagination->page - 3 > 0)
                        <span class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500"
                            aria-current="page">...</span>
                    @endif

                    @foreach (range(1, $pagination->last) as $page)
                        @if ($page > $pagination->page - 2 && $page < $pagination->page + 2)
                            <a href="{{ route('services.index', ['page' => $page]) }}" @class([
                                'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium',
                                'text-indigo-600 border-indigo-500' => $page === $pagination->page,
                                'text-gray-500 hover:text-gray-700' => $page !== $pagination->page,
                            ])
                                aria-current="page">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($pagination->page + 2 < $pagination->last)
                        <span class="inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500"
                            aria-current="page">...</span>
                    @endif

                    @if ($pagination->page + 2 <= $pagination->last)
                        <a href="{{ route('services.index', ['page' => $pagination->last]) }}" @class([
                            'inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium text-gray-500 hover:text-gray-700',
                        ])
                            aria-current="page">{{ $pagination->last }}</a>
                    @endif
                </div>

                <div class="-mt-px flex w-0 flex-1 justify-end">
                    @if ($pagination->page < $pagination->last)
                        <a href="{{ route('services.index', ['page' => $pagination->page + 1]) }}"
                            class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            <span class="hidden md:block">{{ __('Next') }}</span>
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                                    clip-rule="evenodd" />s
                            </svg>
                        </a>
                    @endif
                </div>
            </nav>
        </section>
    </main>
</x-app-layout>
