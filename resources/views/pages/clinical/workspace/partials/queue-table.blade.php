<table class="w-full text-sm mb-2">
    <thead>
        <tr class="text-left text-slate-500 border-b border-slate-200">
            <th class="py-2 pr-4">No</th>
            <th class="py-2 pr-4">No. Visit</th>
            <th class="py-2 pr-4">Pasien</th>
            @if ($showDoctor ?? true)<th class="py-2 pr-4">Dokter</th>@endif
            <th class="py-2 pr-4">Status</th>
            @if ($showBilling ?? false)<th class="py-2 pr-4">Bayar</th>@endif
            @unlessrole('nurse')
                <th class="py-2 text-right">Aksi</th>
            @endunlessrole
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $visit)
            <tr class="border-b border-slate-100 last:border-0 {{ (string) request('highlight') === (string) $visit->id ? 'bg-emerald-50' : '' }} {{ ($clickableRow ?? false) ? 'cursor-pointer hover:bg-slate-50' : '' }}"
                @if ($clickableRow ?? false) x-on:click="Livewire.navigate('{{ route('visits.show', $visit->id) }}')" @endif>
                <td class="py-2.5 pr-4 font-bold">{{ $visit->queueLabel() }}</td>
                <td class="py-2.5 pr-4 font-semibold">{{ $visit->visit_number }}</td>
                <td class="py-2.5 pr-4">
                    @role('nurse')
                        <span class="font-medium">{{ $visit->patient->name ?? '-' }}</span>
                    @else
                        <a href="{{ route('visits.show', $visit->id) }}" wire:navigate class="font-medium hover:text-brand-600">{{ $visit->patient->name ?? '-' }}</a>
                    @endrole
                    <span class="text-xs text-slate-400 ml-1">{{ $visit->patient->code ?? '' }}</span>
                </td>
                @if ($showDoctor ?? true)<td class="py-2.5 pr-4">{{ $visit->doctor->user->name ?? '-' }}</td>@endif
                <td class="py-2.5 pr-4">
                    @php
                        $badge = match ($visit->clinical_status) {
                            'WAITING' => 'badge-warning',
                            'CALLED' => 'badge-info',
                            'IN_TREATMENT' => 'badge-success',
                            'DONE' => 'badge-neutral',
                            'SIGNED' => 'badge-success',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ $visit->clinical_status }}</span>
                </td>
                @if ($showBilling ?? false)
                    <td class="py-2.5 pr-4">
                        <span class="badge {{ $visit->billing_status === 'PAID' ? 'badge-success' : 'badge-warning' }}">
                            {{ $visit->billing_status === 'PAID' ? 'Lunas' : 'Belum Bayar' }}
                        </span>
                    </td>
                @endif
                @unlessrole('nurse')
                <td class="py-2.5">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('visits.show', $visit->id) }}" wire:navigate
                            class="text-sm text-brand-600 hover:text-brand-700">Buka</a>
                        @if ($visit->clinical_status === \App\Models\Visit::STATUS_WAITING)
                            @canany(['write vital sign', 'write anamnesis'])
                                <button type="button" x-on:click="drawer = '{{ $visit->id }}'"
                                    class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">Isi Triase</button>
                            @endcanany
                        @endif
                        {{-- Progres klinis (diperiksa/selesai) hanya dokter & manajemen.
                             Suster memajukan via triase otomatis, bukan tombol ini. --}}
                        @role('doctor|manajemen')
                            @can('update visit')
                                @foreach (($transitions[$visit->clinical_status] ?? []) as $next)
                                    @if ($next !== \App\Models\Visit::STATUS_CALLED)
                                        <form action="{{ route('visits.status', $visit->id) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $next }}" />
                                            <button type="submit" class="text-sm text-slate-500 hover:text-emerald-600">
                                                → {{ $next }}
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                            @endcan
                        @endrole
                    </div>
                </td>
                @endunlessrole
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-6">
                <p class="text-sm text-slate-500">Kosong.</p>
            </td></tr>
        @endforelse
    </tbody>
</table>
