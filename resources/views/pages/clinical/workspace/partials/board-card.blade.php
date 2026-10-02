@php
    $nextLabel = [
        \App\Models\Visit::STATUS_CALLED => 'Panggil ke Dental Chair',
        \App\Models\Visit::STATUS_IN_TREATMENT => 'Mulai Periksa',
        \App\Models\Visit::STATUS_DONE => 'Selesaikan',
    ];
    $primary = null;
    foreach (($transitions[$visit->clinical_status] ?? []) as $candidate) {
        $primary = $candidate;
        break;
    }
    $openDrawer = ($drawerFor ?? 'triage') === 'triage';
@endphp
<div class="rounded-2xl border {{ (string) request('highlight') === (string) $visit->id ? 'border-emerald-400 ring-2 ring-emerald-300' : 'border-slate-200' }} bg-white p-4 shadow-sm hover:shadow-md transition-shadow {{ $openDrawer ? 'cursor-pointer' : '' }}"
    @if ($openDrawer) x-on:click="drawer = '{{ $visit->id }}'" @endif>
    <div class="flex items-center justify-between gap-2">
        <span class="inline-flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-sm font-bold text-white">{{ $visit->queueLabel() }}</span>
        <span class="badge {{ $visit->clinical_status === 'WAITING' ? 'badge-warning' : ($visit->clinical_status === 'IN_TREATMENT' ? 'badge-success' : ($visit->clinical_status === 'CALLED' ? 'badge-info' : 'badge-neutral')) }}">{{ $visit->clinical_status }}</span>
    </div>
    @unless ($hideDoctor ?? false)
        <p class="mt-2 text-xs font-semibold text-slate-500">{{ $visit->doctor->user->name ?? '-' }}</p>
    @endunless
    <p class="mt-0.5 text-base font-bold text-slate-900">@role('nurse'){{ $visit->patient->name ?? '-' }}@else<a href="{{ route('visits.show', $visit->id) }}" wire:navigate class="hover:text-brand-600">{{ $visit->patient->name ?? '-' }}</a>@endrole</p>
    <p class="text-xs text-slate-400">{{ $visit->visit_number }} · No. RM {{ $visit->patient->code ?? '-' }}@if ($visit->appointment?->time_start)· Janji {{ \Carbon\Carbon::parse($visit->appointment->time_start)->format('H:i') }}@endif</p>
    @if (in_array($visit->clinical_status, \App\Models\Visit::QUEUE_STATUSES, true))
        <p class="mt-1 text-xs font-semibold text-amber-600">Tunggu <span class="wait-timer" data-since="{{ $visit->created_at->toIso8601String() }}">--:--</span></p>
    @endif
    @if ($showBilling ?? false)
        <p class="mt-1"><span class="badge {{ $visit->billing_status === 'PAID' ? 'badge-success' : 'badge-warning' }}">{{ $visit->billing_status === 'PAID' ? 'Lunas' : 'Belum Bayar' }}</span></p>
    @endif
    <div class="mt-3 space-y-2" @if ($openDrawer) x-on:click.stop @endif>
        @if ($visit->clinical_status === \App\Models\Visit::STATUS_WAITING)
            @canany(['write vital sign', 'write anamnesis'])
                <button type="button" x-on:click.stop="drawer = '{{ $visit->id }}'"
                    class="clickable-primary w-full px-4 py-2.5 rounded-xl text-sm">Isi Triase</button>
            @endcanany
        @endif
        {{-- Progres klinis hanya dokter & manajemen. Suster via triase otomatis. --}}
        @role('doctor|manajemen')
            @can('update visit')
                @if ($primary && $visit->clinical_status !== \App\Models\Visit::STATUS_WAITING)
                    <form action="{{ route('visits.status', $visit->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="{{ $primary }}" />
                        <button type="submit" class="clickable-primary w-full px-4 py-2.5 rounded-xl text-sm">{{ $nextLabel[$primary] ?? ('→ '.$primary) }}</button>
                    </form>
                @endif
            @endcan
        @endrole
    </div>
</div>
