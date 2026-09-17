<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InstallmentService extends Service
{
    private $limit = 10;

    private function setFilter(&$inst, ?object $filter = null)
    {
        if ($filter) {
            if (isset($filter->status)) {
                $inst->orWhere(DB::raw('LOWER(transactions.patient_code)'), strtolower("%$filter->keyword%"));
                $inst->orWhere(DB::raw('LOWER(patients.name)'), 'like', strtolower("%$filter->keyword%"));
                $inst->orWhere(DB::raw('LOWER(transactions.sequence)'), "%$filter->keyword%");
            }

            if (isset($filter->status)) {
                $inst->orWhere(DB::raw('LOWER(installments.status)'), 'like', strtolower("%$filter->status%"));
            }

            if (isset($filter->due_date)) {
                $inst->orWhere(DB::raw('LOWER(installments.due_date)'), 'like', strtolower("%$filter->due_date%"));
            }

            if (isset($filter->page)) {
                // limit ditambahkan: OFFSET tanpa LIMIT tidak valid di sqlite.
                $inst->offset(($filter->page - 1) * $this->limit)->limit($this->limit);
            }
        }
    }

    public function getPagination(?object $filter = null): object
    {
        try {
            $inst_count = DB::table('installments')
                ->join('transactions', 'installments.transaction_id', '=', 'transactions.id')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->count();

            return (object) [
                'page' => $filter->page,
                'limit' => $this->limit,
                'last' => ceil($inst_count / $this->limit),
                'total' => $inst_count,
            ];
        } catch (\Throwable $th) {
            $this->writeLog('InstallmentService@pagination', $th);

            return (object) [
                'page' => 1,
                'limit' => $this->limit,
                'last' => 1,
                'total' => 0,
            ];
        }
    }

    public function readInstallments(?object $filter = null): Collection
    {
        try {
            $inst = DB::table('installments')
                ->join('transactions', 'installments.transaction_id', '=', 'transactions.id')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->whereNull('transactions.canceled_at')
                ->select('installments.*', 'installments.transaction_id as id', 'transactions.sequence', 'patients.name as patient_name', 'transactions.patient_code');

            $this->setFilter($inst, $filter);
            $inst = $inst->orderBy('installments.created_at')->get();

            $instID = $inst->pluck('transaction_id')->toArray();
            $steps = DB::table('installment_steps')
                ->whereIn('installment_id', $instID)
                ->get();

            $inst->each(function ($item) use ($steps) {
                $item->steps = $steps->where('installment_id', $item->transaction_id);
                $item->total = $item->amount;
                $item->paid = $item->steps->where('status', 'PAID')->sum('amount');
                $item->rest = $item->total - $item->paid;
                $item->due_date = $item->steps->max('due_date');
            });

            return $inst;
        } catch (\Throwable $th) {
            $this->writeLog('InstallmentService@readInstallments', $th);

            return collect([]);
        }
    }

    public function readInstallmentByID(string $id): object
    {
        try {
            $inst = DB::table('installments')
                ->join('transactions', 'installments.transaction_id', '=', 'transactions.id')
                ->join('patients', 'transactions.patient_id', '=', 'patients.id')
                ->select('installments.*', 'transactions.sequence as transaction_code', 'patients.name as patient_name', 'transactions.patient_code')
                ->where('installments.transaction_id', $id)
                ->first();

            $steps = DB::table('installment_steps')
                ->where('installment_id', $id)
                ->get();

            $inst->steps = $steps;
            $inst->total = $inst->amount;
            $inst->paid = $steps->where('status', 'PAID')->sum('amount');
            $inst->rest = $inst->total - $inst->paid;
            $inst->due_date = $steps->max('due_date');

            return $inst;
        } catch (\Throwable $th) {
            // throw $th;
            $this->writeLog('InstallmentService@readInstallmentByID', $th);

            return (object) [];
        }
    }

    public function createInstallment(object $data): bool
    {
        try {
            DB::table('installments')->insert([
                'transaction_id' => $data->transaction_id,
                'amount' => $data->amount,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $data->steps->each(function ($step) use ($data) {
                DB::table('installment_steps')->insert([
                    'installment_id' => $data->transaction_id,
                    'transaction_id' => $data->transaction_id,
                    'type' => $step->type,
                    'step' => $step->step,
                    'due_date' => $step->due_date,
                    'paid_date' => null,
                    'amount' => $step->amount,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            return true;
        } catch (\Throwable $th) {
            $this->writeLog('InstallmentService@createInstallment', $th);

            return false;
        }
    }

    public function payInstallment(string $id, object $data): bool
    {
        try {
            DB::table('installment_steps')
                ->where('installment_id', $id)
                ->where('id', $data->step_id)
                ->update([
                    'status' => 'PAID',
                    'paid_date' => now(),
                    'updated_at' => now(),
                ]);

            $isPaid = DB::table('installment_steps')
                ->where('installment_id', $id)
                ->where('status', 'PENDING')
                ->count() == 0;

            if ($isPaid) {
                DB::table('installments')
                    ->where('transaction_id', $id)
                    ->update([
                        'status' => 'PAID',
                        'updated_at' => now(),
                    ]);
            }

            return true;
        } catch (\Throwable $th) {
            $this->writeLog('InstallmentService@payInstallment', $th);

            return false;
        }
    }

    public function deleteInstallment(string $id): bool
    {
        try {
            DB::table('installment_steps')
                ->where('installment_id', $id)
                ->delete();

            DB::table('installments')
                ->where('transaction_id', $id)
                ->delete();

            return true;
        } catch (\Throwable $th) {
            $this->writeLog('InstallmentService@deleteInstallment', $th);

            return false;
        }
    }
}
