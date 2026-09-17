<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReports;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportTransactions(Request $request)
    {
        $report = new MonthlyReports($request);
        $filename = $report->export();

        return response()->download($filename)->deleteFileAfterSend();
    }
}
