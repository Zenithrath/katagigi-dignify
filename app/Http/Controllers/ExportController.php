<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReports;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportTransactions(Request $request)
    {
        // D-04: export XLSX dibangkitkan dari halaman omzet → butuh read turnover.
        $this->authorize('read turnover');
        $report = new MonthlyReports($request);
        $filename = $report->export();

        return response()->download($filename)->deleteFileAfterSend();
    }
}
