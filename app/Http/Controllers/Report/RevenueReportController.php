<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    /**
     * Perbandingan pendapatan tahun berjalan vs tahun sebelumnya (YoY),
     * per bulan: ditagihkan, terkumpul, dan pertumbuhan %.
     */
    public function index(Request $request)
    {
        $this->authorize('read turnover');

        $year = (int) $request->input('year', date('Y'));
        $prev = $year - 1;
        $months = range(1, 12);

        $rows = [];
        foreach ($months as $m) {
            $rows[] = (object) [
                'month' => $m,
                'invoiced' => $this->invoiced($year, $m),
                'invoiced_prev' => $this->invoiced($prev, $m),
                'collected' => $this->collected($year, $m),
                'collected_prev' => $this->collected($prev, $m),
            ];
        }

        $total = (object) [
            'invoiced' => array_sum(array_column($rows, 'invoiced')),
            'invoiced_prev' => array_sum(array_column($rows, 'invoiced_prev')),
            'collected' => array_sum(array_column($rows, 'collected')),
            'collected_prev' => array_sum(array_column($rows, 'collected_prev')),
        ];

        return view('pages.report.revenue.index', [
            'year' => $year,
            'prev' => $prev,
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    private function invoiced(int $year, int $month): float
    {
        return (float) Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', '!=', Invoice::STATUS_VOID)
            ->sum('total');
    }

    private function collected(int $year, int $month): float
    {
        return (float) InvoicePayment::whereHas('invoice', fn ($q) => $q
            ->whereYear('invoices.created_at', $year)
            ->whereMonth('invoices.created_at', $month))
            ->sum('amount');
    }

    public static function growth(float $now, float $before): ?float
    {
        if ($before <= 0) {
            return null;
        }

        return ($now - $before) / $before * 100;
    }
}
