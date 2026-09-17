<?php

namespace App\Http\Controllers;

use App\Services\InstallmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstallmentController extends Controller
{
    private $installmentService;

    public function __construct(InstallmentService $installmentService)
    {
        $this->installmentService = $installmentService;
    }

    private function getHumanizedInstallmentPhrase(string $type, int $step = 0, string $status = 'PENDING', bool $skipStatus = false): string
    {

        $phrase = '';

        switch ($type) {
            case 'DOWN_PAYMENT':
                $phrase = __('general.phrases.down_payment');
                break;
            case 'INSTALLMENT':
                $phrase = __('general.phrases.installment').' '.$step;
                break;
            case 'BALLOON':
                $phrase = __('general.phrases.balloon');
                break;
            default:
                $phrase = __('general.phrases.full_payment');
                break;
        }

        if ($skipStatus) {
            return $phrase;
        }

        if ($status === 'PAID') {
            $phrase .= ' ('.__('general.phrases.paid').')';
        } else {
            $phrase .= ' ('.__('general.phrases.pending').')';
        }

        return $phrase;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filter = (object) [
            'keyword' => $request->keyword,
            'due_date' => $request->due_date,
            'page' => $request->page ?? 1,
        ];

        return view('pages.installments.index', [
            'installments' => $this->installmentService->readInstallments($filter),
            'pagination' => $this->installmentService->getPagination($filter),
            'getType' => function ($type, $step, $status = 'PENDING') {
                return $this->getHumanizedInstallmentPhrase($type, $step, $status);
            },
            'toRupiah' => function ($amount) {
                return $this->toRupiah($amount);
            },
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
     * @param  string  $id
     * @return Response
     */
    public function show($id)
    {
        return view('pages.installments.detail', [
            'data' => $this->installmentService->readInstallmentByID($id),
            'getType' => function ($type, $step, $status = 'PENDING') {
                return $this->getHumanizedInstallmentPhrase($type, $step, $status, true);
            },
            'toRupiah' => function ($amount) {
                return $this->toRupiah($amount);
            },
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  string  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
