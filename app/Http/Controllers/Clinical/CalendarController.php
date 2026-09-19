<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read appointment');

        $month = $request->input('month', date('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            $start = Carbon::now()->startOfMonth();
            $month = $start->format('Y-m');
        }
        $end = $start->copy()->endOfMonth();

        $counts = DB::table('appointments')
            ->selectRaw('date, count(*) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('canceled_at')
            ->groupBy('date')
            ->pluck('total', 'date');

        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $cursor->toDateString();
                $week[] = [
                    'date' => $key,
                    'day' => $cursor->day,
                    'inMonth' => $cursor->month === $start->month,
                    'isToday' => $key === date('Y-m-d'),
                    'count' => (int) ($counts[$key] ?? 0),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        $day = $request->input('day', date('Y-m-d'));
        $dayList = DB::table('appointments')
            ->whereDate('date', $day)
            ->orderBy('time_start')
            ->get();

        return view('pages.clinical.calendar.index', [
            'month' => $month,
            'monthLabel' => $start->locale('id')->isoFormat('MMMM YYYY'),
            'prevMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $start->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'day' => $day,
            'dayList' => $dayList,
        ]);
    }
}
