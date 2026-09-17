<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceOrtodonsiaSeeder extends Seeder
{
    private $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orto = DB::table('categories')->where('name', 'Ortodonsia')->first();
        $dataOrto = (object) [
            'id' => $orto->id,
            'code' => $orto->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Behel Metal',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 2499000,
                'upper_price' => 4000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pindahan Px Behel',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 1750000,
                'upper_price' => 2500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Kontrol Behel Px Internal',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 550000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Kontrol Behel Px Eksternal',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 250000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Lepas Behel',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Ganti Kawat Behel',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 40000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pembersihan Lem Behel',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Elastik 10 pieces',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 25000,
                'upper_price' => 1500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Peninggian Gigit',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 50000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Hawley Retainer',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 550000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Clear Retainer',
                'category_id' => $dataOrto->id,
                'description' => null,
                'lower_price' => 550000,
                'upper_price' => 1000000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataOrto->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
