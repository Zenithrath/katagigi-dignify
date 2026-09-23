{{-- Baris KPI: SEMUA kartu memakai pola yang sama (kartu putih, icon berwarna,
     label uppercase, angka besar, hint) — memakai komponen kpi-card.
     Pendapatan hanya untuk manajemen/admin; dokter & perawat operasional. --}}
<section data-widget="kpi-row" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    @if ($widget->show_revenue)
        <x-dashboard.kpi-card
            icon="banknote" tone="emerald" :badge="__('dashboard.kpi.this_month')"
            :label="$widget->scoped ? __('dashboard.kpi.revenue_own') : __('dashboard.kpi.revenue_clinic')"
            :value="$widget->revenue" :hint="__('dashboard.kpi.from_notes', ['count' => number_format($widget->transactions, 0, ',', '.')])" />
    @endif

    @if ($widget->patients !== null)
        <x-dashboard.kpi-card
            icon="users" tone="blue" :badge="__('dashboard.kpi.database')"
            :label="__('dashboard.kpi.patients_total')"
            :value="number_format($widget->patients, 0, ',', '.')" :hint="__('dashboard.kpi.patients_total_hint')" />
    @endif

    @if ($widget->new_patients !== null)
        <x-dashboard.kpi-card
            icon="user-plus" tone="amber" :badge="'+'.number_format($widget->new_patients, 0, ',', '.').' '.__('dashboard.kpi.new_badge')"
            badge-tone="emerald"
            :label="__('dashboard.kpi.patients_new')"
            :value="number_format($widget->new_patients, 0, ',', '.')" :hint="__('dashboard.kpi.patients_new_hint')" />
    @endif

    @if ($widget->medical_records !== null)
        <x-dashboard.kpi-card
            icon="user-check" tone="emerald" :badge="__('dashboard.kpi.this_month')"
            :label="__('dashboard.kpi.visits')"
            :value="number_format($widget->transactions, 0, ',', '.')"
            unit="{{ __('dashboard.kpi.visits_unit') }}" :hint="__('dashboard.kpi.visits_hint')" />
    @elseif ($widget->patients === null)
        <x-dashboard.kpi-card
            icon="clipboard-list" tone="teal" :badge="__('dashboard.kpi.this_month')"
            :label="__('dashboard.kpi.records')"
            :value="number_format($widget->medical_records ?? 0, 0, ',', '.')" :hint="__('dashboard.kpi.records_hint')" />
    @endif
</section>
