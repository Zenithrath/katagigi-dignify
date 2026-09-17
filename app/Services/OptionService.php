<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class OptionService extends Service
{
    public function getServiceList(): Collection|array
    {
        try {
            return DB::table('services')
                ->join('categories', 'services.category_id', '=', 'categories.id')
                ->where('services.is_active', true)
                ->select([
                    'services.id',
                    'services.name',
                    'categories.name as category',
                    'services.code',
                    'services.lower_price',
                    'services.upper_price',
                ])->get();
        } catch (Throwable $th) {
            $this->writeLog('OptionService::getServiceList', $th);

            return [];
        }
    }
}
