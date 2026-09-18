<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $doctors = DB::table('doctors')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->select('doctors.*', 'users.name')
            ->get(['users.id as id', 'users.name', 'doctors.*'])
            ->map(function ($d) {
                $d->share_percentage = 30;
                $d->share_overproduction = 35;

                if (! is_null($d->niptk) || $d->niptk != '') {
                    $d->share_percentage += 5;
                    $d->share_overproduction += 5;
                }

                $d->share_rontgent = 20;

                return $d;
            });

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t 23:59:59');

        if (isset($request->start_date)) {
            $start_date = date('Y-m-d', strtotime($request->start_date));
        }

        if (isset($request->end_date)) {
            $end_date = date('Y-m-d 23:59:59', strtotime($request->end_date));
        }

        $dcx = [];

        foreach ($doctors as $d) {
            $shifts = 0;
            $previous_time = '2000-01-01 09:00:00';
            $is_morning_shift = true;
            $regularIncome = 0;
            $overproductionIncome = 0;

            // Portabel sqlite/mysql/pgsql: uraikan JSON services di PHP
            // (pengganti CTE json_array_elements + row_number Postgres).
            // num = urutan transaksi per appointment_datetime (mulai 1);
            // num > target dokter (default 55) = overproduksi.
            $target = (int) ($d->target ?? 55);
            $rows = DB::table('transactions')
                ->where('appointment_datetime', '>=', $start_date)
                ->where('appointment_datetime', '<=', $end_date)
                ->where('doctor_id', $d->user_id)
                ->whereNull('canceled_at')
                ->orderBy('appointment_datetime')
                ->get(['id', 'appointment_datetime', 'services']);

            $nonRontgenTransactions = [];
            $rontgenTransaction = [];
            $num = 0;
            foreach ($rows as $row) {
                $num++;
                foreach ((array) json_decode($row->services) as $svc) {
                    $svc = (array) $svc;
                    $entry = (object) [
                        'id' => $row->id,
                        'trx_id' => $row->id,
                        'num' => $num,
                        'doctor_id' => $d->user_id,
                        'appointment_datetime' => $row->appointment_datetime,
                        'svc_code' => $svc['code'] ?? null,
                        'svc_name' => $svc['name'] ?? null,
                        'quantity' => (int) ($svc['quantity'] ?? 0),
                        'price' => (int) ($svc['price'] ?? 0),
                        'discount' => (int) ($svc['discount'] ?? 0),
                        'undiscount' => ((int) ($svc['price'] ?? 0)) * ((int) ($svc['quantity'] ?? 0)),
                        'svc_id' => $svc['id'] ?? null,
                        'is_overproduction' => $num > $target ? 1 : 0,
                    ];
                    if (($svc['code'] ?? null) === 'UM021') {
                        $rontgenTransaction[] = $entry;
                    } else {
                        $nonRontgenTransactions[] = $entry;
                    }
                }
            }

            foreach ($nonRontgenTransactions as $tx) {
                if (date('Y-m-d', strtotime($tx->appointment_datetime)) == date('Y-m-d', strtotime($previous_time))) {
                    if ($is_morning_shift && date('l', strtotime($tx->appointment_datetime)) != 'Sunday' && strtotime(date('H:i:s', strtotime($tx->appointment_datetime))) >= strtotime('15:00:00')) {
                        $shifts++;
                        $previous_time = $tx->appointment_datetime;
                        $is_morning_shift = false;
                    }

                    if ($is_morning_shift && date('l', strtotime($tx->appointment_datetime)) == 'Sunday' && strtotime(date('H:i:s', strtotime($tx->appointment_datetime))) >= strtotime('14:00:00')) {
                        $shifts++;
                        $previous_time = $tx->appointment_datetime;
                        $is_morning_shift = false;
                    }
                } else {
                    $shifts++;
                    $previous_time = $tx->appointment_datetime;
                }

                if ($tx->is_overproduction) {
                    $overproductionIncome += $tx->undiscount - $tx->discount;
                } else {
                    $regularIncome += $tx->undiscount - $tx->discount;
                }
            }

            $rontgentIncome = 0;

            foreach ($rontgenTransaction as $tx) {
                $rontgentIncome += $tx->undiscount - $tx->discount;
            }

            $dcx[] = (object) [
                'doctor' => $d,
                'regular_income' => $regularIncome,
                'regular_percentage' => $d->share_percentage,
                'regular_share' => $regularIncome * $d->share_percentage / 100,
                'overproduction_income' => $overproductionIncome,
                'overproduction_percentage' => $d->share_overproduction,
                'overproduction_share' => $overproductionIncome * $d->share_overproduction / 100,
                'rontgent_income' => $rontgentIncome,
                'rontgent_percentage' => $d->share_rontgent,
                'rontgent_share' => $rontgentIncome * $d->share_rontgent / 100,
                'shifts' => $shifts,
                'shift_fee' => $shifts * 50000,
                'share_total' => ($regularIncome * $d->share_percentage / 100) + ($overproductionIncome * $d->share_overproduction / 100) + ($rontgentIncome * $d->share_rontgent / 100)
                    + ($shifts * 50000),
                'non_rontgen_transactions' => $nonRontgenTransactions,
                'rontgen_transactions' => $rontgenTransaction,
            ];
        }

        return view('pages.report.salaries.index', [
            'doctors' => $dcx,
            'start_date' => $start_date,
            'end_date' => date('Y-m-d', strtotime($end_date)),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
