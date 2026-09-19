<x-app-layout>
    <x-slot:title>Visit {{ $visit->visit_number }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Visit {{ $visit->visit_number }}</h1>
                <p>{{ $visit->patient->name ?? '-' }} — {{ $visit->doctor->user->name ?? '-' }} — {{ $visit->visit_date?->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge {{ $visit->isSigned() ? 'badge-success' : 'badge-info' }}">{{ $visit->clinical_status }}</span>
                <span class="badge badge-neutral">{{ $visit->billing_status }}</span>
            </div>
        </section>

        <x-flash-alerts />

        <div class="content-card p-8">
            <dl class="detail-list">
                <div class="data-container">
                    <dt>Pasien</dt>
                    <dd class="font-semibold">{{ $visit->patient->name ?? '-' }}
                        <span class="text-xs text-slate-400 font-normal ml-1">{{ $visit->patient->code ?? '' }}</span>
                    </dd>
                </div>
                <div class="data-container">
                    <dt>Dokter</dt>
                    <dd>{{ $visit->doctor->user->name ?? '-' }}</dd>
                </div>
                <div class="data-container">
                    <dt>Cabang</dt>
                    <dd>{{ $visit->branch->name ?? '-' }}</dd>
                </div>
                @if ($visit->appointment)
                    <div class="data-container">
                        <dt>Appointment</dt>
                        <dd>
                            <a href="{{ route('appointments.show', $visit->appointment_id) }}" class="text-brand-600 hover:text-brand-700">
                                {{ $visit->appointment->date }} ({{ $visit->appointment->time_start }}–{{ $visit->appointment->time_end }})
                            </a>
                        </dd>
                    </div>
                @endif
                @if ($visit->notes)
                    <div class="data-container">
                        <dt>Catatan</dt>
                        <dd>{{ $visit->notes }}</dd>
                    </div>
                @endif
            </dl>

            @can('update visit')
                @if (! $visit->isSigned() && ! empty($transitions[$visit->clinical_status]))
                    <div class="mt-4 pt-4 border-t border-slate-200 flex flex-wrap gap-2">
                        @foreach ($transitions[$visit->clinical_status] as $next)
                            <form action="{{ route('visits.status', $visit->id) }}" method="post">
                                @csrf
                                <input type="hidden" name="status" value="{{ $next }}" />
                                <button type="submit" class="clickable-primary px-5 py-2 rounded-xl">→ {{ $next }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            @endcan

            <section class="mt-6 pt-6 border-t border-slate-200">
                <h3 class="text-sm font-bold text-slate-700 mb-1">Isi klinis</h3>
                <p class="text-sm text-slate-500">Anamnesis, pemeriksaan SOAP, odontogram, diagnosis, tindakan, resep, rencana perawatan, dan lampiran menyusul pada task berikutnya.</p>
            </section>
        </div>
    </main>
</x-app-layout>
