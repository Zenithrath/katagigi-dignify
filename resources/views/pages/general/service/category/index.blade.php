@extends('layouts.main-layout')

@section('_title', __('general.service.index._title'))
@section('header')
    <x-main-header title="{{ __('features.category') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="GENERAL.SERVICE" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('general.category.index._title') }}</h1>
                <p>{{ __('general.category.index._subtitle') }}</p>
            </div>

            <div class="flex gap-2">
                {{-- @can('read service') --}}
                <a href="{{ route('services.index') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('general.service.index._nav') }}
                </a>
                {{-- @endcan --}}

                {{-- @can('create category') --}}
                <a href="{{ route('categories.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('general.category.index.action.add') }}
                </a>
                {{-- @endcan --}}
            </div>
        </section>

        @if (Session::has('success'))
            <div class="mb-8">
                <x-alerts.success message="{{ Session::get('success') }}" />
            </div>
        @endif

        @if (Session::has('error'))
            <div class="mb-8">
                <x-alerts.failed message="{{ Session::get('error') }}" />
            </div>
        @endif

        <section class="table-content">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="column">{{ __('No.') }}</th>
                        <th scope="col" class="index-column">{{ __('general.category.index.table.code') }}</th>
                        <th scope="col" class="index-column">{{ __('general.category.index.table.name') }}</th>
                        <th scope="col" class="column">{{ __('general.category.index.table.services') }}</th>
                        <th scope="col" class="action-column">
                            <span class="sr-only"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($categoryList) > 0)
                        @foreach ($categoryList as $category)
                            <tr>
                                <td class="column">{{ $loop->iteration }}</td>
                                <td class="index-column w-64 truncate">
                                    {{ $category->code }}
                                </td>
                                <td class="index-column w-64 truncate">
                                    {{ $category->name }}
                                </td>
                                <td class="column">
                                    {{ $category->services . ' ' . __('general.category.index.table.services') }}
                                </td>
                                <td class="action-column">
                                    <div class="flex gap-2">
                                        {{-- @can('update category') --}}
                                        <a href="{{ route('categories.edit', ['category' => $category->id]) }}"
                                            class="h-full">
                                            {{ __('general.category.index.action.edit') }}
                                            <span class="sr-only">{{ $category->name }}</span>
                                        </a>
                                        {{-- @endcan --}}
                                        {{-- @can('delete category') --}}
                                        <form action="{{ route('categories.destroy', ['category' => $category->id]) }}"
                                            method="post">
                                            @csrf
                                            @method('delete')
                                            <button class="text-danger-600 hover:text-danger-500 active:text-danger-700"
                                                type="submit">{{ __('general.category.index.action.delete') }}<span
                                                    class="sr-only">{{ $category->name }}</span></button>
                                        </form>
                                        {{-- @endcan --}}
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
    </main>
@endsection
