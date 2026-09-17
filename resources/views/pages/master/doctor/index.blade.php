@extends('layouts.main-layout')

@section('_title', 'Dashboard')
@section('header')
    <x-main-header title="{{ __('features.doctor') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="MASTER.DOCTOR" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>{{ __('master.doctor.index.title') }}</h1>
                <p>{{ __('master.doctor.index.subtitle') }}</p>
            </div>

            @can('create doctor')
                <a href="{{ route('doctors.create') }}" class="clickable-primary py-2 px-4 rounded-md">
                    {{ __('master.doctor.index.buttons.add') }}
                </a>
            @endcan
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
                                <td class="index-column flex gap-4 items-center w-80 overflow-hidden truncate">
                                    @if ($doctor->profile_picture)
                                        <img src="{{ asset('storage/' . $doctor->profile_picture) }}"
                                            alt="{{ $doctor->name . "'s Profile Picture" }}"
                                            class="w-12 h-12 object-cover object-center rounded-full" />
                                    @else
                                        <div class="w-12 h-12 fill-none stroke-1 stroke-slate-900 dark:stroke-slate-100">
                                            <x-icons.user-circle />
                                        </div>
                                    @endif
                                    <div class="flex flex-col w-64">
                                        <span class="font-semibold w-full truncate">{{ $doctor->name }}</span>
                                        <span class="text-gray-500 w-full truncate">{{ $doctor->email }}</span>
                                    </div>
                                </td>
                                <td class="column">{{ $doctor->nipp ?? '-' }}</td>
                                <td class="column">
                                    {{ $doctor->is_active ? __('master.doctor.index.table.active') : __('master.doctor.index.table.inactive') }}
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
@endsection
