<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\IncomeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    private $service;

    public function __construct(IncomeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('read turnover');
        $doctorID = null;

        if (Auth::user()->hasRole('doctor')) {
            $doctorID = Auth::user()->id;
        }

        return view('pages.report.income.index', [
            'doctors' => $this->service->readAvailableDoctors($doctorID),
            'services' => $this->service->readAvailableServices(),
        ]);
    }

    public function lookupTransactionReport(Request $request)
    {
        $this->authorize('read turnover');
        try {
            $doctorID = null;

            if (Auth::user()->hasRole('doctor')) {
                $doctorID = Auth::user()->id;
            }

            return [
                'meta' => (object) [
                    'code' => 200,
                    'message' => 'OK',
                ],
                'data' => $this->service->readTransactionOverview($request, $doctorID),
            ];
        } catch (\Throwable $th) {
            // throw $th;
            return [
                'meta' => (object) [
                    'code' => 500,
                    'message' => 'Internal Server Error',
                ],
                'data' => null,
                'error' => $th->getMessage(),
            ];
        }
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
