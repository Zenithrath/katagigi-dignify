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
        @can('read appointment')
            <x-sidebar-link href="{{ route('calendar.index') }}" :active="request()->routeIs('calendar.*')" icon="calendar" :collapsible="$collapsible">{{ __('navigation.items.calendar') }}</x-sidebar-link>
        @endcan
        @can('read service')
            <x-sidebar-link href="{{ route('services.index') }}" :active="request()->routeIs('services.*', 'categories.*')" icon="briefcase-medical" :collapsible="$collapsible">{{ __('navigation.sidenav.general.service') }}</x-sidebar-link>
        @endcan
    </ul>
</div>

@can('read visit')
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sections.clinical') }}</x-sidebar-section>
        <ul class="nav-list">
            <x-sidebar-link href="{{ route('workspace.index') }}" :active="request()->routeIs('workspace.*')" icon="stethoscope" :collapsible="$collapsible">{{ __('navigation.items.workspace') }}</x-sidebar-link>
            <x-sidebar-link href="{{ route('visits.index') }}" :active="request()->routeIs('visits.*')" icon="list-ordered" :collapsible="$collapsible">{{ __('navigation.items.queue') }}</x-sidebar-link>
        </ul>
    </div>
@endcan

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
            @can('read transaction')
                <x-sidebar-link href="{{ route('invoices.index') }}" :active="request()->routeIs('invoices.*')" icon="receipt-text" :collapsible="$collapsible">{{ __('navigation.items.invoices') }}</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('incomes.index') }}" :active="request()->routeIs('incomes.*')" icon="chart-column" :collapsible="$collapsible">{{ __('navigation.sidenav.report.turnover') }}</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('doctor-fees.index') }}" :active="request()->routeIs('doctor-fees.*')" icon="wallet" :collapsible="$collapsible">{{ __('navigation.items.doctor_fees') }}</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('finance-report.index') }}" :active="request()->routeIs('finance-report.*')" icon="file-text" :collapsible="$collapsible">{{ __('navigation.items.finance_report') }}</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('revenue-report.index') }}" :active="request()->routeIs('revenue-report.*')" icon="activity" :collapsible="$collapsible">{{ __('navigation.items.revenue_report') }}</x-sidebar-link>
            @endcan
            @can('read assistant payroll')
                <x-sidebar-link href="{{ route('assistant-payroll.index') }}" :active="request()->routeIs('assistant-payroll.*')" icon="wallet" :collapsible="$collapsible">{{ __('navigation.items.assistant_payroll') }}</x-sidebar-link>
            @endcan
            @role('manajemen')
                <x-sidebar-link href="{{ route('transactions.index') }}" :active="false" icon="clipboard-check" badge="via nota" :collapsible="$collapsible">{{ __('navigation.sidenav.report.approval') }}</x-sidebar-link>
            @endrole
        </ul>
    </div>
@endcanany

@canany(['manage satusehat', 'manage whatsapp'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sections.integrations') }}</x-sidebar-section>
        <ul class="nav-list">
            @can('manage satusehat')
                <x-sidebar-link href="{{ route('satusehat.index') }}" :active="request()->routeIs('satusehat.*')" icon="activity" :collapsible="$collapsible">SATUSEHAT</x-sidebar-link>
            @endcan
            @can('manage whatsapp')
                <x-sidebar-link href="{{ route('whatsapp.index') }}" :active="request()->routeIs('whatsapp.*')" icon="message-circle" :collapsible="$collapsible">WhatsApp</x-sidebar-link>
            @endcan
        </ul>
    </div>
@endcanany

@can('read audit log')
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sections.administration') }}</x-sidebar-section>
        <ul class="nav-list">
            <x-sidebar-link href="{{ route('audit-logs.index') }}" :active="request()->routeIs('audit-logs.*')" icon="shield-check" :collapsible="$collapsible">{{ __('navigation.items.audit_logs') }}</x-sidebar-link>
        </ul>
    </div>
@endcan

@canany(['read inventory', 'read expense', 'manage branch'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sections.operational') }}</x-sidebar-section>
        <ul class="nav-list">
            @can('read inventory')
                <x-sidebar-link href="{{ route('inventory.index') }}" :active="request()->routeIs('inventory.*')" icon="package" :collapsible="$collapsible">{{ __('navigation.items.inventory') }}</x-sidebar-link>
            @endcan
            @can('read expense')
                <x-sidebar-link href="{{ route('expenses.index') }}" :active="request()->routeIs('expenses.*')" icon="banknote" :collapsible="$collapsible">{{ __('navigation.items.expenses') }}</x-sidebar-link>
            @endcan
            @can('manage branch')
                <x-sidebar-link href="{{ route('branches.index') }}" :active="request()->routeIs('branches.*')" icon="building-2" :collapsible="$collapsible">{{ __('navigation.items.branches') }}</x-sidebar-link>
            @endcan
            @can('manage holiday')
                <x-sidebar-link href="{{ route('holidays.index') }}" :active="request()->routeIs('holidays.*')" icon="calendar" :collapsible="$collapsible">{{ __('navigation.items.holidays') }}</x-sidebar-link>
            @endcan
            @canany(['manage attendance', 'read assistant payroll'])
                @if (auth()->user()->can('manage attendance') || auth()->user()->hasRole('nurse'))
                    <x-sidebar-link href="{{ route('attendances.index') }}" :active="request()->routeIs('attendances.*')" icon="clipboard-check" :collapsible="$collapsible">{{ __('navigation.items.attendances') }}</x-sidebar-link>
                @endif
            @endcanany
        </ul>
    </div>
@endcanany
