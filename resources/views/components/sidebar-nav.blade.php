{{-- Sidebar: fokus alur pasien + menu yang diminta (2026-09-25).
     Sisa menu non-inti tetap diparkir: view di backup/views,
     route dikomentari di routes/web.php & routes/integration.php. --}}
@props(['collapsible' => false])

<div class="menu-section">
    <x-sidebar-section :collapsible="$collapsible">Umum</x-sidebar-section>
    <ul class="nav-list">
        <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="layout-grid" :collapsible="$collapsible">Beranda</x-sidebar-link>
        @can('read schedule')
            <x-sidebar-link href="{{ route('schedules.index') }}" :active="request()->routeIs('schedules.*')" icon="calendar-days" :collapsible="$collapsible">Jadwal dan Sesi</x-sidebar-link>
        @endcan
        @can('read appointment')
            <x-sidebar-link href="{{ route('appointments.index') }}" :active="request()->routeIs('appointments.*')" icon="calendar-plus" :collapsible="$collapsible">Janji Temu</x-sidebar-link>
        @endcan
        @can('read appointment')
            <x-sidebar-link href="{{ route('calendar.index') }}" :active="request()->routeIs('calendar.*')" icon="calendar" :collapsible="$collapsible">{{ __('navigation.items.calendar') }}</x-sidebar-link>
        @endcan
        @can('read service')
            <x-sidebar-link href="{{ route('services.index') }}" :active="request()->routeIs('services.*', 'categories.*')" icon="briefcase-medical" :collapsible="$collapsible">Layanan dan Biaya</x-sidebar-link>
        @endcan
    </ul>
</div>

@can('read visit')
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">{{ __('navigation.sections.clinical') }}</x-sidebar-section>
        <ul class="nav-list">
            <x-sidebar-link href="{{ route('workspace.index') }}" :active="request()->routeIs('workspace.*', 'visits.*')" icon="stethoscope" :collapsible="$collapsible">{{ __('navigation.items.workspace') }}</x-sidebar-link>
        </ul>
    </div>
@endcan

@canany(['read patient', 'read medical record'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">Pasien</x-sidebar-section>
        <ul class="nav-list">
            @can('read patient')
                <x-sidebar-link href="{{ route('patients.index') }}" :active="request()->routeIs('patients.*')" icon="users" :collapsible="$collapsible">Informasi Pasien</x-sidebar-link>
            @endcan
            @can('read medical record')
                <x-sidebar-link href="{{ route('medical-records.index') }}" :active="request()->routeIs('medical-records.*')" icon="clipboard-list" :collapsible="$collapsible">Rekam Medis</x-sidebar-link>
            @endcan
        </ul>
    </div>
@endcanany

@role('manajemen|admin')
    @canany(['read doctor', 'read nurse', 'read admin'])
        <div class="menu-section">
            <x-sidebar-section :collapsible="$collapsible">Kepegawaian</x-sidebar-section>
            <ul class="nav-list">
                @can('read doctor')
                    <x-sidebar-link href="{{ route('doctors.index') }}" :active="request()->routeIs('doctors.*')" icon="stethoscope" :collapsible="$collapsible">Dokter</x-sidebar-link>
                @endcan
                @can('read nurse')
                    <x-sidebar-link href="{{ route('nurses.index') }}" :active="request()->routeIs('nurses.*')" icon="heart-pulse" :collapsible="$collapsible">Perawat</x-sidebar-link>
                @endcan
                @can('read admin')
                    <x-sidebar-link href="{{ route('admins.index') }}" :active="request()->routeIs('admins.*')" icon="user-cog" :collapsible="$collapsible">Admin</x-sidebar-link>
                @endcan
            </ul>
        </div>
    @endcanany
@endrole

@canany(['read transaction', 'read turnover'])
    <div class="menu-section">
        <x-sidebar-section :collapsible="$collapsible">Laporan</x-sidebar-section>
        <ul class="nav-list">
            @role('manajemen|admin|nurse')
                <x-sidebar-link href="{{ route('installments.index') }}" :active="request()->routeIs('installments.*')" icon="receipt" :collapsible="$collapsible">Installments</x-sidebar-link>
            @endrole
            @can('read transaction')
                <x-sidebar-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')" icon="arrow-left-right" :collapsible="$collapsible">Transaksi</x-sidebar-link>
            @endcan
            @can('read turnover')
                <x-sidebar-link href="{{ route('incomes.index') }}" :active="request()->routeIs('incomes.*')" icon="chart-column" :collapsible="$collapsible">Income</x-sidebar-link>
            @endcan
            @can('read transaction')
                <x-sidebar-link href="{{ route('invoices.index') }}" :active="request()->routeIs('invoices.*')" icon="receipt-text" :collapsible="$collapsible">{{ __('navigation.items.invoices') }}</x-sidebar-link>
            @endcan
        </ul>
    </div>
@endcanany

{{-- PARKED 2026-09-24: jasa dokter, keuangan, payroll, integrasi
     (SATUSEHAT/WhatsApp), audit, operasional (inventory/beban/cabang/
     libur/absensi). Kembalikan dari backup/views + uncomment route. --}}
