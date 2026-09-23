<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Nurse;
use App\Models\NurseAttendance;
use App\Services\Payroll\AssistantOvertimeCalculator as Calc;
use Illuminate\Http\Request;

class AssistantPayrollController extends Controller
{
    /**
     * Rekap payroll asisten per bulan: hari masuk, menit reguler,
     * menit & jam lembur (45 mnt = 1 jam), dan upah lembur.
     * Hari libur (tabel holidays): lembur dihitung sejak jam masuk.
     */
    public function index(Request $request)
    {
        $this->authorize('read assistant payroll');

        $month = $request->input('month', date('Y-m'));
        [$y, $m] = array_map('intval', explode('-', $month) + [date('Y'), date('m')]);

        $rate = (int) config('clinic.overtime_rate');
        $grace = (int) config('clinic.rounding_grace_minutes');

        $holidays = Holiday::whereYear('date', $y)->whereMonth('date', $m)
            ->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->flip();

        $nurses = Nurse::join('users', 'users.id', '=', 'nurses.user_id')
            ->select('nurses.user_id', 'users.name')
            ->orderBy('users.name')
            ->get();

        $rows = [];
        foreach ($nurses as $n) {
            $logs = NurseAttendance::where('user_id', $n->user_id)
                ->whereYear('date', $y)->whereMonth('date', $m)
                ->orderBy('date')->get();

            $days = 0;
            $regular = 0;
            $otMinutes = 0;
            foreach ($logs as $log) {
                $days++;
                $isHoliday = isset($holidays[$log->date->format('Y-m-d')]);
                $work = Calc::toMinutes($log->clock_out) - Calc::toMinutes($log->clock_in);
                $ot = Calc::overtimeMinutes($log->clock_in, $log->clock_out, $log->scheduled_end, $isHoliday);
                $otMinutes += $ot;
                $regular += max(0, $work - $ot);
            }
            $otHours = Calc::overtimeHours($otMinutes, $grace);
            $rows[] = (object) [
                'name' => $n->name,
                'days' => $days,
                'regular_minutes' => $regular,
                'overtime_minutes' => $otMinutes,
                'overtime_hours' => $otHours,
                'overtime_pay' => Calc::overtimePay($otHours, $rate),
            ];
        }

        return view('pages.report.payroll.index', [
            'rows' => $rows,
            'month' => sprintf('%04d-%02d', $y, $m),
            'rate' => $rate,
            'total_pay' => array_sum(array_column($rows, 'overtime_pay')),
        ]);
    }
}
