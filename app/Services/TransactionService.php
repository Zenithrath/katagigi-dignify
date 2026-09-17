<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Throwable;

class TransactionService extends Service
{
    private function initSelect(object $filter): Builder
    {
        $query = DB::table('transactions')
            ->select([
                'transactions.id',
                'transactions.patient_id',
                'transactions.patient_code',
                'transactions.doctor_id',
                'transactions.doctor_nipp',
                'transactions.doctor_name',
                'transactions.services',
                'transactions.price',
                'transactions.payment_method',
                'transactions.created_at',
                'transactions.canceled_at',
                'patients.name as patient_name',
                'patients.phone as patient_phone',
            ])
            ->join('patients', 'transactions.patient_id', '=', 'patients.id')
            ->orderBy('transactions.created_at', 'desc');

        if (isset($filter->keyword)) {
            $query->whereRaw('LOWER(patient_name) LIKE ?', '%'.strtolower($filter->keyword).'%');
            $query->orWhereRaw('LOWER(patients.name) LIKE ?', '%'.strtolower($filter->keyword).'%');
            $query->orWhereRaw('LOWER(patient_code) LIKE ?', '%'.strtolower($filter->keyword).'%');
        }

        if (isset($filter->since)) {
            $query->where('transactions.created_at', '>=', $filter->since);
        }

        if (isset($filter->until)) {
            $query->where('transactions.created_at', '<=', $filter->until);
        }

        return $query;
    }

    public function readAllTransactions(?object $filter = null): LengthAwarePaginator|Exception
    {
        try {
            return $this->initSelect($filter)
                ->paginate(20)
                ->through(function ($a) {
                    $services = (array) json_decode($a->services);
                    $serviceList = array_map(function ($item) {
                        return $item->code.' - '.$item->name;
                    }, $services);

                    return (object) [
                        'patient' => (object) [
                            'id' => $a->patient_id,
                            'code' => $a->patient_code,
                            'name' => $a->patient_name,
                            'phone' => $a->patient_phone,
                        ],
                        'doctor' => (object) [
                            'id' => $a->doctor_id,
                            'nipp' => $a->doctor_nipp,
                            'name' => $a->doctor_name,
                        ],
                        'services' => $serviceList,
                        'payment_method' => $a->payment_method,
                        'id' => $a->id,
                        'price' => $a->price,
                        'created_at' => date('c', strtotime($a->created_at)),
                        'canceled_at' => $a->canceled_at ? date('c', strtotime($a->canceled_at)) : null,
                    ];
                })
                ->withQueryString();
        } catch (Exception $err) {
            $this->writeLog('TransactionService::readAllTransactions', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function countTotalData(mixed $filter = null): object
    {
        try {
            $tx = DB::table('transactions')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->selectRaw('count(*) as counter');

            if (isset($filter->keyword)) {
                $tx->whereRaw('LOWER(patient_name) LIKE ?', '%'.strtolower($filter->keyword).'%');
                $tx->orWhereRaw('LOWER(patients.name) LIKE ?', '%'.strtolower($filter->keyword).'%');
                $tx->orWhereRaw('LOWER(patient_code) LIKE ?', '%'.strtolower($filter->keyword).'%');
            }

            if (isset($filter->since)) {
                $tx->where('transactions.created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $tx->where('transactions.created_at', '<=', $filter->until);
            }

            return $tx->first();
        } catch (Throwable $th) {
            $this->writeLog('MedicalRecordService::countTotalData', $th);

            return new Collection;
        }
    }

    public function readLatestTransactions(mixed $filter = null): Collection|Throwable
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            return DB::table('transactions')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->orderBy('transactions.created_at', 'desc')
                ->get([
                    'transactions.id',
                    'transactions.patient_id',
                    'transactions.patient_code',
                    'patients.name as patient_name',
                    'patients.phone as patient_phone',
                    'transactions.doctor_id',
                    'transactions.doctor_nipp',
                    'transactions.doctor_name',
                    'transactions.services',
                    'transactions.price',
                    'transactions.created_at',
                ])->map(
                    function ($a) {
                        $services = (array) json_decode($a->services);

                        return (object) [
                            'patient' => (object) [
                                'id' => $a->patient_id,
                                'code' => $a->patient_code,
                                'name' => $a->patient_name,
                                'phone' => $a->patient_phone,
                            ],
                            'doctor' => (object) [
                                'id' => $a->doctor_id,
                                'nipp' => $a->doctor_nipp,
                                'name' => $a->doctor_name,
                            ],
                            'services' => $services,
                            'id' => $a->id,
                            'price' => $a->price,
                            'created_at' => date('c', strtotime($a->created_at)),
                        ];
                    }
                );
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readLatestTransactions', $th);
            throw $th;
        }
    }

    public function readTransactionByFilter(object $filter): Collection|Throwable
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            $transactions = DB::table('transactions')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->orderBy('transactions.created_at', 'desc');

            if (isset($filter->keyword)) {
                $transactions->whereRaw('LOWER(patient_name) LIKE ?', '%'.strtolower($filter->keyword).'%');
                $transactions->orWhereRaw('LOWER(patients.name) LIKE ?', '%'.strtolower($filter->keyword).'%');
                $transactions->orWhereRaw('LOWER(patient_code) LIKE ?', '%'.strtolower($filter->keyword).'%');
            }

            if (isset($filter->since)) {
                $transactions->where('transactions.created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $transactions->where('transactions.created_at', '<=', $filter->until);
            }

            return $transactions->get([
                'transactions.id',
                'transactions.patient_id', // 'transactions.patient_name',
                'transactions.patient_code',
                'transactions.doctor_id',
                'transactions.doctor_nipp',
                'transactions.doctor_name',
                'transactions.services',
                'transactions.price',
                'transactions.payment_method',
                'transactions.created_at',
                'transactions.canceled_at',
                'patients.name as patient_name',
                'patients.phone as patient_phone',
            ])->map(
                function ($a) {
                    $services = (array) json_decode($a->services);
                    $serviceList = array_map(function ($item) {
                        return $item->code.' - '.$item->name;
                    }, $services);

                    return (object) [
                        'patient' => (object) [
                            'id' => $a->patient_id,
                            'code' => $a->patient_code,
                            'name' => $a->patient_name,
                            'phone' => $a->patient_phone,
                        ],
                        'doctor' => (object) [
                            'id' => $a->doctor_id,
                            'nipp' => $a->doctor_nipp,
                            'name' => $a->doctor_name,
                        ],
                        'services' => $serviceList,
                        'payment_method' => $a->payment_method,
                        'id' => $a->id,
                        'price' => $a->price,
                        'created_at' => date('c', strtotime($a->created_at)),
                        'canceled_at' => $a->canceled_at ? date('c', strtotime($a->canceled_at)) : null,
                    ];
                }
            );
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readTransactionByFilter', $th);
            throw $th;
        }
    }

    public function readTransactionByID(string $id): ?object
    {
        try {
            $trx = DB::table('transactions')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->leftJoin('installments', 'installments.transaction_id', '=', 'transactions.id')
                ->where('transactions.id', $id)->first([
                    'transactions.id',
                    'transactions.patient_id',
                    'transactions.patient_code',
                    'transactions.patient_name as recent_patient_name',
                    'patients.phone as patient_phone',
                    'transactions.doctor_id',
                    'transactions.doctor_nipp',
                    'transactions.doctor_name',
                    'transactions.services',
                    'transactions.price',
                    'transactions.payment_method',
                    'transactions.created_at',
                    'transactions.canceled_at',
                    'patients.name as patient_name',
                    'transactions.appointment_id',
                    'transactions.next_schedule',
                    'transactions.discount',
                    'transactions.price',
                    'transactions.billing',
                    'transactions.cancel_reason',
                    'transactions.sequence',
                    'transactions.voucher_code',
                    'transactions.current_payment',
                    'transactions.has_installment',
                    'transactions.referenced_installment_id',
                    'installments.amount as installment_amount',
                    'installments.status as installment_status',
                ]);

            $trx->installment_steps = DB::table('installment_steps')
                ->where('installment_id', $trx->referenced_installment_id)
                ->orderBy('step')
                ->get();

            return $trx;
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readTransactionByID', $th);
            throw $th;
        }
    }

    public function readPayableAppointments(): Collection|Throwable
    {
        try {
            $trx = DB::table('appointments')
                ->select([
                    'patient_id',
                    'patient_code',
                    'patient_name',
                    'patient_phone',
                    'date',
                    'id',
                    'services',
                    'doctor_id',
                    'doctor_nipp',
                    'doctor_name',
                    'time_start',
                    'time_end',
                ])
                ->whereNotNull('confirmed_at')
                ->whereNull('canceled_at')
                ->whereNull('paid_at')
                ->get();

            $patient_id = $trx->pluck('patient_id')->toArray();

            $remaining_insts = DB::table('installment_steps')
                ->join('installments', 'installments.transaction_id', '=', 'installment_steps.installment_id')
                ->join('transactions', 'transactions.id', '=', 'installments.transaction_id')
                ->whereIn('installments.patient_id', $patient_id)
                ->whereNull('transactions.canceled_at')
                ->where('installment_steps.status', 'PENDING')
                ->get([
                    'installment_steps.id',
                    'installment_steps.installment_id',
                    'installment_steps.transaction_id',
                    'installment_steps.type',
                    'installment_steps.step',
                    'installment_steps.due_date',
                    'installment_steps.amount',
                    'installment_steps.status',
                ]);

            return $trx->map(function ($a) use ($remaining_insts) {
                return (object) [
                    'patient' => (object) [
                        'id' => $a->patient_id,
                        'code' => $a->patient_code,
                        'name' => $a->patient_name,
                        'phone' => $a->patient_phone,
                    ],
                    'doctor' => [
                        'id' => $a->doctor_id,
                        'name' => $a->doctor_name,
                        'nipp' => $a->doctor_nipp,
                    ],
                    'remaining_installment_steps' => $remaining_insts->where('installment_id'),
                    'services' => $a->services,
                    'time_start' => $a->time_start,
                    'time_end' => $a->time_end,
                    'id' => $a->id,
                    'date' => $a->date,
                ];
            });
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readPayableAppointments', $th);
            throw $th;
        }
    }

    public function readAvailableServices(): Collection|Throwable
    {
        try {
            return DB::table('services')
                ->join('categories', 'services.category_id', '=', 'categories.id')
                ->select([
                    'services.id',
                    'services.name',
                    'services.code',
                    'categories.name as category',
                    'services.upper_price',
                    'services.lower_price',
                ])
                ->get();
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readAvailableServices', $th);
            throw $th;
        }
    }

    public function readAssistants(): Collection|Throwable
    {
        try {
            return DB::table('nurses')
                ->join('users', 'users.id', '=', 'nurses.user_id')
                ->select(['id as id', 'nipp', 'niptk', 'name'])
                ->get();
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::readAssistants', $th);
            throw $th;
        }
    }

    public function createTransaction(object $data): string|Throwable
    {
        try {
            $appointment = DB::table('appointments')->where('id', $data->appointment_id)
                ->first();
            $services = DB::table('services')
                ->join('categories', 'categories.id', '=', 'services.category_id')
                ->whereIn('services.id', $data->service_id)
                ->select([
                    'services.id',
                    'services.name',
                    'services.code',
                    'categories.name as category',
                ])
                ->get();
            $assistant = ($data->assistant_id ?? null) ? DB::table('nurses')
                ->join('users', 'users.id', '=', 'nurses.user_id')
                ->where('nurses.user_id', $data->assistant_id)
                ->select(['nurses.user_id as id', 'users.name', 'nurses.nipp'])
                ->first() : (object) [
                    'id' => null,
                    'name' => null,
                    'nipp' => null,
                ];

            $pricedServices = array_map(function ($id, $price, $quantity, $discount) use ($services) {
                $index = array_search($id, array_column($services->toArray(), 'id'));

                return [
                    'id' => $id,
                    'class' => 'service',
                    'price' => (float) $price,
                    'quantity' => (float) $quantity,
                    'subtotal' => (float) $price * $quantity,
                    'discount' => (float) $discount ?? 0,
                    'code' => $services[$index]->code,
                    'name' => $services[$index]->name,
                    'category' => $services[$index]->category,
                ];
            }, $data->service_id, $data->service_price, $data->service_quantity, $data->service_discount);

            $instSvc = null;

            if (isset($data->installment_step)) {
                $instSvc = DB::table('installment_steps')
                    ->join('installments', 'installments.transaction_id', '=', 'installment_steps.installment_id')
                    ->join('transactions', 'installments.transaction_id', '=', 'transactions.id')
                    ->where('installment_steps.id', '=', $data->installment_step)
                    ->first(['transactions.id as transaction_id', 'transactions.sequence as transaction_code', 'transactions.appointment_datetime', 'installment_steps.*']);

                array_push($pricedServices, [
                    'id' => $instSvc->id,
                    'class' => 'installment',
                    'price' => (float) $instSvc->amount,
                    'quantity' => 1,
                    'subtotal' => (float) $instSvc->amount,
                    'discount' => 0,
                    'code' => 'INSTALLMENT',
                    'name' => 'Angsuran '.$instSvc->step.' Trx. Tgl. '.Carbon::parse($instSvc->appointment_datetime)->format('d M Y'),
                    'category' => 'INSTALLMENT',
                ]);
            }

            $totalPrice = 0;
            $totalDiscount = 0;

            foreach ($pricedServices as $service) {
                $totalPrice += $service['subtotal'];
                $totalDiscount += $service['discount'];
            }

            $id = Uuid::uuid4();

            DB::table('transactions')->insert([
                'id' => $id,
                // Pengganti trigger Postgres generate_transaction_sequence:
                // nomor nota = 2 digit tahun + urutan tahun berjalan.
                'sequence' => $this->nextSequence(),
                'patient_id' => $appointment->patient_id,
                'patient_name' => $appointment->patient_name,
                'patient_code' => $appointment->patient_code,
                'patient_phone' => $appointment->patient_phone,
                'doctor_id' => $appointment->doctor_id,
                'doctor_name' => $appointment->doctor_name,
                'doctor_nipp' => $appointment->doctor_nipp,
                'nurse_id' => $assistant->id,
                'nurse_nipp' => $assistant->nipp,
                'nurse_name' => $assistant->name,
                'appointment_id' => $appointment->id,
                'appointment_datetime' => date('Y-m-d H:i:s', strtotime($appointment->date.' '.$appointment->time_start)),
                'services' => json_encode($pricedServices),
                'next_schedule' => $data->next_schedule ?? null,
                'price' => $totalPrice,
                'discount' => $totalDiscount,
                'billing' => $totalPrice - $totalDiscount,
                'current_payment' => $totalPrice - $totalDiscount,
                'payment_method' => $data->payment_method ?? 'CASH',
                'voucher_code' => $data->voucher_code ?? null,
                'referenced_installment_id' => null,
            ]);

            if (isset($data->installment_step)) {
                $instID = $instSvc->installment_id;

                DB::table('transactions')->where('id', $id)
                    ->update(['referenced_installment_id' => $instID]);

                DB::table('installment_steps')->where('id', $data->installment_step)
                    ->update([
                        'status' => 'PAID',
                        'transaction_id' => $id,
                        'paid_at' => now(),
                        'updated_at' => now(),
                    ]);

                $isComplete = DB::table('installment_steps')->where('installment_id', $instID)
                    ->where('status', 'PENDING')
                    ->count() === 0;

                if ($isComplete) {
                    DB::table('installments')->where('transaction_id', $instID)
                        ->update(['status' => 'PAID', 'updated_at' => now()]);
                }
            }

            DB::table('appointments')->where('id', $appointment->id)->update([
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            if (isset($data->is_installment) && $data->is_installment === 'on') {
                $installmentID = $id;
                $installmentSteps = [];

                $installmentPeriod = $data->installment_period ?? 1;
                $downPayment = $data->down_payment_amount ?? 0;
                $installmentAmount = ($totalPrice - $totalDiscount - $downPayment) / $installmentPeriod;
                $total = $downPayment + $installmentAmount * $installmentPeriod;

                DB::table('installments')->insert([
                    'transaction_id' => $installmentID,
                    'patient_id' => $appointment->patient_id,
                    'amount' => $total,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $installmentSteps[] = [
                    'id' => Uuid::uuid4(),
                    'installment_id' => $installmentID,
                    'transaction_id' => $id,
                    'type' => 'DOWN_PAYMENT',
                    'step' => 0,
                    'due_date' => date('Y-m-d'),
                    'paid_at' => date('Y-m-d H:i:s'),
                    'amount' => $downPayment,
                    'status' => 'PAID',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                for ($i = 1; $i <= $installmentPeriod; $i++) {
                    $installmentSteps[] = [
                        'id' => Uuid::uuid4(),
                        'installment_id' => $installmentID,
                        'transaction_id' => null,
                        'type' => 'INSTALLMENT',
                        'step' => $i,
                        'due_date' => date('Y-m-d', strtotime("+$i month")),
                        'paid_at' => null,
                        'amount' => $installmentAmount,
                        'status' => 'PENDING',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('installment_steps')->insert($installmentSteps);

                DB::table('transactions')->where('id', $installmentID)->update([
                    'referenced_installment_id' => $installmentID,
                    'has_installment' => true,
                    'current_payment' => $downPayment,
                ]);
            }

            return $id;
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::createTransaction', $th);
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Nomor nota: (2 digit tahun) * 100000 + urutan dalam tahun berjalan.
     * Menggantikan trigger Postgres generate_transaction_sequence.
     */
    public function nextSequence(): int
    {
        $yearPart = (int) date('y');

        $max = DB::table('transactions')
            ->where('sequence', '>=', $yearPart * 100000)
            ->where('sequence', '<', ($yearPart + 1) * 100000)
            ->max('sequence');

        return $max ? ((int) $max) + 1 : $yearPart * 100000 + 1;
    }

    public function reschedule(string $id, string $date): bool|Throwable
    {
        try {
            return DB::table('transactions')->where('id', $id)->update([
                'next_schedule' => $date,
            ]);
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::reschedule', $th);
            throw $th;
        }
    }

    public function cancel(string $id, string $reason): bool|Throwable
    {
        try {
            $installmentBind = DB::table('transactions')->where('id', $id)->first(['referenced_installment_id', 'services']);

            if ($installmentBind->referenced_installment_id) {
                if ($installmentBind->referenced_installment_id === $id) {
                    DB::table('installment_steps')
                        ->where('installment_id', $id)
                        ->update([
                            'status' => 'CANCELED',
                            'updated_at' => now(),
                        ]);

                    DB::table('installments')
                        ->where('transaction_id', $id)
                        ->update([
                            'status' => 'CANCELED',
                            'updated_at' => now(),
                        ]);
                } else {
                    $installmentID = $installmentBind->referenced_installment_id;
                    DB::table('installment_steps')
                        ->where('installment_id', $installmentID)
                        ->where('transaction_id', $id)
                        ->where('status', 'PAID')
                        ->update([
                            'status' => 'PENDING',
                            'transaction_id' => null,
                            'paid_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('transactions')->where('id', $id)->update([
                'cancel_reason' => $reason,
                'canceled_at' => date('Y-m-d H:i:s'),
            ]);

            $appointmentID = DB::table('transactions')->where('id', $id)
                ->first('appointment_id')->appointment_id;

            $isValidTransactionExists = DB::table('transactions')
                ->where('appointment_id', $appointmentID)
                ->whereNull('canceled_at')
                ->exists();

            if (! $isValidTransactionExists) {
                DB::table('appointments')->where('id', $appointmentID)->update([
                    'paid_at' => null,
                ]);
            }

            return true;
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::cancel', $th);
            throw $th;
        }
    }

    public function getRemainingInstallmentSteps(string $patient_id): Collection|Throwable
    {
        try {
            return DB::table('installment_steps')
                ->join('installments', 'installments.transaction_id', '=', 'installment_steps.installment_id')
                ->where('installments.patient_id', $patient_id)
                ->where('installment_steps.status', 'PENDING')
                ->get([
                    'installment_steps.id',
                    'installment_steps.installment_id',
                    'installment_steps.transaction_id',
                    'installment_steps.type',
                    'installment_steps.step',
                    'installment_steps.due_date',
                    'installment_steps.paid_date',
                    'installment_steps.amount',
                    'installment_steps.status',
                    'installment_steps.created_at',
                    'installment_steps.updated_at',
                ]);
        } catch (Throwable $th) {
            $this->writeLog('TransactionService::getRemainingInstallmentSteps', $th);
            throw $th;
        }
    }
}
