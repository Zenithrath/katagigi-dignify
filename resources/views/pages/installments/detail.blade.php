@extends('layouts.main-layout')

@section('_title', __('general.installment.detail._title'))
@section('header')
    <x-main-header title="{{ __('features.installment') }}" />
@endsection

@section('navigator')
    <x-main-sidenav feature="REPORT.INSTALLMENT" />
@endsection

@section('footer')
    <x-main-footer />
@endsection

@section('content')
    <main class="mb-auto px-8 pt-8 pb-12">
        <div class="flex gap-4 items-center">
            <a href="{{ route('installments.index') }}" class="clickable-ghost w-8 h-8 rounded-md">
                <x-icons.chevron-left />
            </a>
            <h1> {{ __('general.installment.detail._title') }} </h1>
        </div>

        <section id="detail" class="mt-4 content-card p-8">
            <dl class="detail-list">
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.transaction_code') }}</dt>
                    <dd>{{ $data->transaction_code }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.patient_name') }}</dt>
                    <dd>{{ $data->patient_name }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.patient_code') }}</dt>
                    <dd>{{ $data->patient_code }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.amount') }}</dt>
                    <dd>{{ $toRupiah($data->amount) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.status') }}</dt>
                    <dd>{{ $data->status }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.total') }}</dt>
                    <dd>{{ $toRupiah($data->total) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.paid') }}</dt>
                    <dd>{{ $toRupiah($data->paid) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.rest') }}</dt>
                    <dd>{{ $toRupiah($data->rest) }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.due_date') }}</dt>
                    <dd>{{ $data->due_date }}</dd>
                </div>
                <div class="data-container">
                    <dt>{{ __('general.installment.detail.labels.steps') }}</dt>
                    <dd>
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('general.table.number_no') }}</th>
                                    <th>{{ __('general.installment.index.table.steps') }}</th>
                                    <th>{{ __('general.installment.index.table.amount') }}</th>
                                    <th>{{ __('general.installment.index.table.due_date') }}</th>
                                    <th>{{ __('general.installment.index.table.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data->steps as $step)
                                    <tr>
                                        <td class="column">{{ $loop->iteration }}</td>
                                        <td class="column text-left">{{ $getType($step->type, $step->step) }}</td>
                                        <td class="column text-right">{{ $toRupiah($step->amount) }}</td>
                                        <td class="column text-center">{{ $step->due_date }}</td>
                                        <td class="column text-center">{{ $step->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </dd>
                </div>
            </dl>
        </section>
    </main>
@endsection
