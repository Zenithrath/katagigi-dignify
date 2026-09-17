<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class IncomeService extends Service
{
    public function readAvailableDoctors(?string $doctorID = null): Collection|Throwable
    {
        try {
            if (! is_null($doctorID)) {
                return DB::table('doctors')
                    ->join('users', 'users.id', '=', 'doctors.user_id')
                    ->where('doctors.user_id', $doctorID)
                    ->select([
                        'id', 'users.name', 'doctors.nipp', 'doctors.niptk',
                    ])->get();
            }

            return DB::table('doctors')
                ->join('users', 'users.id', '=', 'doctors.user_id')->select([
                    'id', 'users.name', 'doctors.nipp', 'doctors.niptk',
                ])->get();
        } catch (Throwable $th) {
            $this->writeLog('IncomeService::readAvailableDoctors', $th);
            throw $th;
        }
    }

    public function readAvailableServices(): Collection|Throwable
    {
        try {
            return DB::table('services')
                ->select(['id', 'code', 'name'])
                ->get();
        } catch (Throwable $th) {
            $this->writeLog('IncomeService::readAvailableServices', $th);
            throw $th;
        }
    }

    public function readTransactionOverview(?object $filter = null, ?string $doctorID = null)
    {
        try {
            $transactions = DB::table('transactions')
                ->whereNull('canceled_at');

            if (isset($filter->doctor)) {
                $transactions->where('doctor_id', $filter->doctor);
            }

            if (isset($filter->since)) {
                $transactions->where('created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $until = Carbon::parse($filter->until)->addDays(1);
                $transactions->where('created_at', '<=', $until);
            }

            if (isset($doctorID)) {
                $transactions->where('doctor_id', $doctorID);
            }

            if (! isset($filter->since) && ! isset($filter->until)) {
                $transactions->whereMonth('created_at', date('m'));
                $transactions->whereYear('created_at', date('Y'));
            }

            // Agregasi per service di PHP (pengganti CTE json_array_elements
            // Postgres): price*qty, discount, qty per service id.
            $report = [];
            foreach ($transactions->pluck('services') as $json) {
                foreach ((array) json_decode($json) as $item) {
                    $item = (array) $item;
                    if (($item['class'] ?? 'service') !== 'service' || empty($item['id'])) {
                        continue;
                    }
                    $id = $item['id'];
                    $report[$id] ??= ['price' => 0, 'discount' => 0, 'quantity' => 0];
                    $report[$id]['price'] += ((float) ($item['price'] ?? 0)) * ((float) ($item['quantity'] ?? 0));
                    $report[$id]['discount'] += (float) ($item['discount'] ?? 0);
                    $report[$id]['quantity'] += (float) ($item['quantity'] ?? 0);
                }
            }

            $services = DB::table('services')
                ->join('categories', 'categories.id', '=', 'services.category_id')
                ->select([
                    'services.id', 'services.name', 'services.code',
                    'categories.name as category',
                ])
                ->get()
                ->map(function ($service) use ($report) {
                    $service->price = $report[$service->id]['price'] ?? null;
                    $service->discount = $report[$service->id]['discount'] ?? null;
                    $service->quantity = $report[$service->id]['quantity'] ?? null;

                    return $service;
                });

            return $services;
        } catch (Throwable $th) {
            $this->writeLog('IncomeService::readAvailableServices', $th);
            throw $th;
        }
    }

    public function readTransactionsByFilter(?object $filter = null): Collection|Throwable
    {
        try {
            $query = DB::table('transactions');

            if (isset($filter->since)) {
                $query->where('created_at', '>=', $filter->since);
            }

            if (isset($filter->until)) {
                $query->where('created_at', '<=', $filter->until);
            }

            if (isset($filter->doctor)) {
                $query->where('doctor_id', '=', $filter->doctor);
            }

            if (isset($filter->service)) {
                $query->where('services', 'like', $filter->service);
            }

            return $query->get();
        } catch (Throwable $th) {
            $this->writeLog('IncomeService::readTransactionsByFilter', $th);
            throw $th;
        }
    }
}
