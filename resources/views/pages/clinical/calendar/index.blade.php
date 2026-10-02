<x-app-layout>
    <x-slot:title>Kalender Appointment — {{ $monthLabel }}</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>Kalender Appointment</h1>
                <p>{{ $monthLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="clickable-ghost px-3 py-2 rounded-xl text-sm">← Sebelumnya</a>
                <a href="{{ route('calendar.index', ['month' => date('Y-m')]) }}" class="clickable-ghost px-3 py-2 rounded-xl text-sm">Bulan ini</a>
                <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="clickable-ghost px-3 py-2 rounded-xl text-sm">Berikutnya →</a>
            </div>
        </section>

        <div class="content-card overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500 mb-1">
                    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
                        <div class="py-1">{{ $d }}</div>
                    @endforeach
                </div>
                @foreach ($weeks as $week)
                    <div class="grid grid-cols-7 gap-1">
                        @foreach ($week as $cell)
                            <a href="{{ route('calendar.index', ['month' => $month, 'day' => $cell['date']]) }}"
                                class="min-h-16 rounded-xl border p-1.5 text-left transition-colors
                                {{ $cell['inMonth'] ? 'border-slate-200 bg-white hover:border-emerald-400' : 'border-transparent bg-slate-50 text-slate-300' }}
                                {{ $cell['isToday'] ? '!border-emerald-500' : '' }}
                                {{ $day === $cell['date'] ? 'ring-2 ring-emerald-500' : '' }}">
                                <span class="text-xs font-bold {{ $cell['isToday'] ? 'text-emerald-600' : '' }}">{{ $cell['day'] }}</span>
                                @if ($cell['count'] > 0)
                                    <span class="block mt-0.5 text-[11px] font-semibold text-brand-600">{{ $cell['count'] }} janji</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="p-6 pt-2">
                <h3 class="font-bold text-slate-900 mb-2">Jadwal {{ \Carbon\Carbon::parse($day)->locale('id')->isoFormat('dddd, D MMMM YYYY') }} ({{ $dayList->count() }})</h3>
                @forelse ($dayList as $appointment)
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                        <span>
                            <span class="font-semibold">{{ substr($appointment->time_start, 0, 5) }}–{{ substr($appointment->time_end, 0, 5) }}</span>
                            {{ $appointment->patient_name }}
                            <span class="text-slate-400">· {{ $appointment->doctor_name }}</span>
                        </span>
                        <span class="flex items-center gap-2">
                            @if ($appointment->canceled_at)
                                <span class="badge badge-danger">BATAL</span>
                            @elseif ($appointment->confirmed_at)
                                <span class="badge badge-warning">CONFIRMED</span>
                            @else
                                <span class="badge badge-neutral">BOOKED</span>
                            @endif
                            <a href="{{ route('appointments.show', $appointment->id) }}" class="text-brand-600 hover:text-brand-700">Buka</a>
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada appointment hari ini.</p>
                @endforelse
            </div>
        </div>
    </main>
</x-app-layout>
