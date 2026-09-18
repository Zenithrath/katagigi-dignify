<?php

namespace App\Http\Controllers\Report;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\TransactionCancellationRequest;
use App\Services\OptionService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class TransactionController extends Controller
{
    private $service;

    private $optionService;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
        $this->optionService = new OptionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('read transaction');
        $rupiahConverter = function (float $value) {
            return GeneralHelper::floatToRupiah($value);
        };

        return view('pages.report.transaction.index', [
            'transactionList' => $this->service->readAllTransactions($request),
            'toRupiah' => $rupiahConverter,
        ]);
    }

    public function getTransactionsByKeyword(Request $request)
    {
        $this->authorize('read transaction');
        $appointmentList = $this->service->readTransactionByFilter($request);
        $total = $this->service->countTotalData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => (int) $request->page ?? 1,
            'limit' => (int) $limit,
            'last' => (int) ceil($total->counter / $limit),
            'total' => (int) $total->counter,
        ];

        return response()->json([
            'data' => $appointmentList,
            'pagination' => $pagination,
        ], 200);
    }

    public function getTransactionsByID(Request $request, $id)
    {
        $this->authorize('read transaction');
        return response()->json([
            'data' => $this->service->readTransactionByID($id),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create transaction');
        $appointments = $this->service->readPayableAppointments();
        $services = $this->optionService->getServiceList();
        $assistants = $this->service->readAssistants();

        return view('pages.report.transaction.form', [
            'type' => 'create',
            'appointments' => $appointments,
            'services' => $services,
            'assistants' => $assistants,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(TransactionRequest $request)
    {
        $this->authorize('create transaction');
        try {
            if ($id = $this->service->createTransaction((object) $request->validated())) {
                return redirect()->route('transactions.show', ['transaction' => $id])
                    ->with('success', __('messages.transaction.success.oncreate'));
            }
        } catch (Throwable $th) {
            return redirect()->back()->withInput()
                ->with('error', __('messages.transaction.error.oncreate'));
        }
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('read transaction');
        $toRupiah = function ($value) {
            return str_replace('Rp. ', '', GeneralHelper::floatToRupiah($value));
        };

        $transaction = $this->service->readTransactionByID($id);
        $services = json_decode($transaction->services);

        $detail = (object) [
            'patient' => (object) [
                'id' => $transaction->patient_id,
                'code' => $transaction->patient_code,
                'name' => $transaction->patient_name,
                'phone' => $transaction->patient_phone,
            ],
            'doctor' => (object) [
                'id' => $transaction->doctor_id,
                'nipp' => $transaction->doctor_nipp,
                'name' => $transaction->doctor_name,
            ],
            'services' => $services,
            'appointment_id' => $transaction->appointment_id,
            'next_schedule' => $transaction->next_schedule,
            'price' => $transaction->price,
            'discount' => $transaction->discount,
            'billing' => $transaction->billing,
            'current_payment' => $transaction->current_payment,
            'installments' => $transaction->installment_steps,
            'payment_method' => $transaction->payment_method,
            'has_installment' => $transaction->has_installment,
            'id' => $transaction->id,
            'sequence' => $transaction->sequence,
            'voucher_code' => $transaction->voucher_code,
            'cancel_reason' => $transaction->cancel_reason,
            'created_at' => date('c', strtotime($transaction->created_at)),
            'canceled_at' => $transaction->canceled_at ? date('c', strtotime($transaction->canceled_at)) : null,
        ];

        return view('pages.report.transaction.detail', [
            'toRupiah' => $toRupiah,
            'getType' => function ($type, $step, $status = 'PENDING') {
                return $this->getHumanizedInstallmentPhrase($type, $step, $status, true);
            },
            'data' => $detail,
            // V2 usul-kunci-approve: usulan pending + riwayat usulan nota ini.
            'pendingProposal' => TransactionCancellationRequest::with(['proposer', 'decider'])
                ->where('transaction_id', $id)
                ->where('status', 'PROPOSED')
                ->first(),
            'proposalHistory' => TransactionCancellationRequest::with(['proposer', 'decider'])
                ->where('transaction_id', $id)
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function reschedule(Request $request, string $id)
    {
        // D-04: reschedule tanggal kontrol = perubahan nota → butuh update transaction.
        $this->authorize('update transaction');
        if ($this->service->reschedule($id, $request->date) instanceof Throwable) {
            return response(null, 500);
        }

        return response(null, 204);
    }

    public function cancel(Request $request, string $id)
    {
        // V2: batal langsung hanya untuk manajemen.
        // Admin operasional wajib lewat usulan (CancellationController@propose).
        $this->authorize('approve cancellation');

        if ($this->service->cancel($id, $request->cancel_reason) instanceof Throwable) {
            return redirect()->back()->with('error', 'Can\'t cancel this transaction!');
        }

        return redirect()->back();
    }

    // D-06c: edit/update/destroy nota dinonaktifkan di route (ledger final).
}
