{{-- Daftar menu sidebar bersama: dipakai shell desktop + drawer mobile.
     Label via __() agar mengikuti locale aktif. --}}
@props(['collapsible' => false])

<div class="menu-section">
    <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sidenav.general._title') }}</x-sidebar-section>
    <ul class="nav-list">
        <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="layout-grid" :collapsible="$collapsible">{{ __('navigation.sidenav.general.home') }}</x-sidebar-link>
        @can('read schedule')
            <x-sidebar-link href="{{ route('schedules.index') }}" :active="request()->routeIs('schedules.*')" icon="calendar-days" :collapsible="$collapsible">{{ __('navigation.sidenav.general.schedule') }}</x-sidebar-link>
        @endcan
        @can('read appointment')
            <x-sidebar-link href="{{ route('appointments.index') }}" :active="request()->routeIs('appointments.*')" icon="calendar-plus" :collapsible="$collapsible">{{ __('navigation.sidenav.general.appointment') }}</x-sidebar-link>
        @endcan
        @can('read service')
            <x-sidebar-link href="{{ route('services.index') }}" :active="request()->routeIs('services.*', 'categories.*')" icon="briefcase-medical" :collapsible="$collapsible">{{ __('navigation.sidenav.general.service') }}</x-sidebar-link>
        @endcan
    </ul>
</div>

@canany(['read patient', 'read medical record'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sidenav.patient._title') }}</x-sidebar-section>
        <ul class="nav-list">
            @can('read patient')
                <x-sidebar-link href="{{ route('patients.index') }}" :active="request()->routeIs('patients.*')" icon="users" :collapsible="$collapsible">{{ __('navigation.sidenav.patient.master') }}</x-sidebar-link>
            @endcan
            @can('read medical record')
                <x-sidebar-link href="{{ route('medical-records.index') }}" :active="request()->routeIs('medical-records.*')" icon="clipboard-list" :collapsible="$collapsible">{{ __('navigation.sidenav.patient.record') }}</x-sidebar-link>
            @endcan
        </ul>
    </div>
@endcanany

@role('manajemen|admin')
    @canany(['read doctor', 'read nurse', 'read admin'])
        <div class="menu-section">
            <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sidenav.employmentship._title') }}</x-sidebar-section>
            <ul class="nav-list">
                @can('read doctor')
                    <x-sidebar-link href="{{ route('doctors.index') }}" :active="request()->routeIs('doctors.*')" icon="stethoscope" :collapsible="$collapsible">{{ __('navigation.sidenav.employmentship.doctor') }}</x-sidebar-link>
                @endcan
                @can('read nurse')
                    <x-sidebar-link href="{{ route('nurses.index') }}" :active="request()->routeIs('nurses.*')" icon="heart-pulse" :collapsible="$collapsible">{{ __('navigation.sidenav.employmentship.nurse') }}</x-sidebar-link>
                @endcan
                @can('read admin')
                    <x-sidebar-link href="{{ route('admins.index') }}" :active="request()->routeIs('admins.*')" icon="user-cog" :collapsible="$collapsible">{{ __('navigation.sidenav.employmentship.admin') }}</x-sidebar-link>
                @endcan
            </ul>
        </div>
    @endcanany
@endrole

@canany(['read transaction', 'read turnover'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sidenav.report._title') }}</x-sidebar-section>
        <ul class="nav-list">
            @role('manajemen|admin|nurse')
                <x-sidebar-link href="{{ route('installments.index') }}" :active="request()->routeIs('installments.*')" icon="receipt" :collapsible="$collapsible">{{ __('navigation.sidenav.report.installment') }}</x-sidebar-link>
            @endrole
            @can('read transaction')
                <x-sidebar-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')" icon="arrow-left-right" :collapsible="$collapsible">{{ __('navigation.sidenav.report.transaction') }}</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('incomes.index') }}" :active="request()->routeIs('incomes.*')" icon="chart-column" :collapsible="$collapsible">{{ __('navigation.sidenav.report.turnover') }}</x-sidebar-link>
            @endcan
            @role('manajemen')
                <x-sidebar-link href="{{ route('transactions.index') }}" :active="false" icon="clipboard-check" badge="via nota" :collapsible="$collapsible">{{ __('navigation.sidenav.report.approval') }}</x-sidebar-link>
            @endrole
        </ul>
    </div>
@endcanany
