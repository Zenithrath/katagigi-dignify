<x-app-layout>
    <x-slot:title>{{ __('dashboard.header.title_'.$data->role) }}</x-slot:title>

    <div class="space-y-6">
        {{-- Header Ringkasan & Aksi Cepat --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-[22px] bg-white p-6 sm:p-7 border border-slate-200/90 shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-200">
                        <x-lucide-shield-check class="w-3.5 h-3.5" /> {{ $data->roleLabel }}
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('dashboard.header.title_'.$data->role) }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('dashboard.header.subtitle_'.$data->role) }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($data->role === 'admin')
                    <a href="{{ route('patients.create') }}" class="btn-shiny-emerald inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white">
                        <x-lucide-user-plus class="w-4 h-4" /> <span>{{ __('dashboard.action.new_patient') }}</span>
                    </a>
                    <a href="{{ route('transactions.create') }}" class="btn-shiny-emerald inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white">
                        <x-lucide-receipt class="w-4 h-4" /> {{ __('dashboard.action.transaction') }}
                    </a>
                    <a href="{{ route('export-transactions') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                        <x-lucide-download class="w-4 h-4" /> {{ __('dashboard.action.export') }}
                    </a>
                @elseif (in_array($data->role, ['doctor', 'nurse'], true) && auth()->user()->can('read visit'))
                    <a href="{{ route('workspace.index') }}" class="btn-shiny-emerald inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white">
                        <x-lucide-stethoscope class="w-4 h-4" /> <span>{{ __('dashboard.action.workspace') }}</span>
                    </a>
                    <a href="{{ route('visits.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                        <x-lucide-list-ordered class="w-4 h-4" /> {{ __('dashboard.action.queue') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Widget role-gated: urutan & isi ditentukan DashboardController::widgetMap() --}}
        @foreach ($data->widgets as $widget)
            @includeIf('dashboard.widgets.'.$widget->view, ['widget' => $widget->payload, 'data' => $data])
        @endforeach

        @if ($data->widgets->isEmpty())
            <div class="content-card text-center text-sm text-slate-500">
                {{ __('dashboard.empty_widgets') }}
            </div>
        @endif
    </div>
</x-app-layout>
