<?php

namespace App\Services\General;

use App\Services\Service;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceService extends Service
{
    private function initSelect(?object $filter = null)
    {
        $query = DB::table('services')
            ->join('categories', 'category_id', '=', 'categories.id', 'left');

        if (isset($filter->category)) {
            $query->where('category.id', $filter->category);
        }

        if (isset($filter->keyword)) {
            $query->like('services.name', 'like', '%'.$filter->keyword.'%');
        }

        return $query;
    }

    public function readAllServices(?object $filter = null): Collection|Exception
    {
        $page = $filter->page ?? 1;
        $limit = $filter->limit ?? 20;

        try {
            return $this->initSelect($filter)->select([
                'services.id', 'services.name', 'services.code',
                'categories.name as category_name', 'lower_price',
                'upper_price', 'description',
            ])
                ->where('is_active', true)
                ->limit($limit)->offset(($page - 1) * $limit)
                ->get();
        } catch (Exception $err) {
            $this->writeLog('ServiceService::readAllServices', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function countData(?object $filter = null): int|Exception
    {
        try {
            return $this->initSelect($filter)->selectRaw('count(*) as counter')
                ->first()->counter;
        } catch (Exception $err) {
            $this->writeLog('ServiceService::countData', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readAllCategories(): Collection|Exception
    {
        try {
            return DB::table('categories')->get();
        } catch (Exception $err) {
            return new Exception($err->getMessage(), 500);
        }
    }

    public function readServiceByID(string $id): object
    {
        try {
            return DB::table('services')->where('services.id', $id)
                ->join('categories', 'services.category_id', '=', 'categories.id', 'left')
                ->select(['services.id', 'services.name', 'category_id', 'services.code', DB::raw('categories.name as category_name'), 'lower_price', 'upper_price', 'doctor_commision', 'description'])
                ->first();
        } catch (Exception $err) {
            $this->writeLog('ServiceService::readServiceByID', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readCategoryByID(string $id): object
    {
        try {
            return DB::table('categories')->where('id', $id)->first();
        } catch (Exception $err) {
            $this->writeLog('ServiceService::readCategoryByID', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function readServicesByIDList(array $IDList)
    {
        try {
            return DB::table('services')->whereIn('services.id', $IDList)
                ->join('categories', 'categories.id', '=', 'services.category_id')
                ->select(['services.id', 'services.name', 'categories.name as category_name', 'services.code'])
                ->get();
        } catch (\Throwable $th) {
            $this->writeLog('ServiceService::readServicesByIDList', $th);

            return new Exception($th->getMessage(), 500);
        }
    }

    public function generateServiceCode(string $prefix): string
    {
        $lastServiceCategory = DB::table('services')->where('code', 'like', $prefix.'%')->orderBy('code', 'desc')->first();
        if ($lastServiceCategory) {
            $lastCode = $lastServiceCategory->code;
            $countPrefix = strlen($prefix);
            $lastCode = substr($lastCode, $countPrefix);
            $lastCode = (int) $lastCode;
            $lastCode++;
            $lastCode = sprintf('%03d', $lastCode);
            $lastCode = $prefix.$lastCode;
        } else {
            $lastCode = $prefix.'001';
        }

        return $lastCode;
    }

    public function createService(mixed $valid, string $code): bool|Exception
    {
        try {
            return DB::table('services')->insert([
                'id' => Uuid::uuid4(),
                'name' => $valid->name,
                'code' => $code,
                'description' => $valid->description,
                'lower_price' => $valid->lower_price,
                'upper_price' => $valid->upper_price,
                'doctor_commision' => $valid->doctor_commision,
                'category_id' => $valid->category_id,
            ]);
        } catch (Exception $err) {
            $this->writeLog('ServiceService::createService', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function updateService(mixed $valid, string $code, string $id): bool|Exception
    {
        try {
            return DB::table('services')->where('id', $id)->update([
                'name' => $valid->name,
                'description' => $valid->description,
                'code' => $code,
                'lower_price' => $valid->lower_price,
                'upper_price' => $valid->upper_price,
                'doctor_commision' => $valid->doctor_commision,
                'category_id' => $valid->category_id,
            ]);
        } catch (Exception $err) {
            $this->writeLog('ServiceService::updateService', $err);

            return new Exception($err->getMessage(), 500);
        }
    }

    public function deleteService($id): int|Exception
    {
        try {
            return DB::table('services')->where('id', $id)->delete();
        } catch (Exception $err) {
            $this->writeLog('ServiceService::deleteService', $err);

            return new Exception($err->getMessage(), 500);
        }
    }
}
