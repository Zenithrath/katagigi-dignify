<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceBedahMulutSeeder extends Seeder
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
        $bm = DB::table('categories')->where('name', 'Bedah Mulut')->first();
        $dataBm = (object) [
            'id' => $bm->id,
            'code' => $bm->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi Tanpa Penyulit',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi Dengan Penyulit',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 250000,
                'upper_price' => 1500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi Goyang Derajat 3',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi 8 Non Operasi',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 300000,
                'upper_price' => 1500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Odontektomi',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 1250000,
                'upper_price' => 2500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Frenektomi',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 2500000,
                'upper_price' => 2500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bedah Minor',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 400000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Operculektomi',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Dry Socket',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Insisi Abses Sonde',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Insisi Abses Anastesi & Scalpel',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Jahitan',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Angkat Jahitan',
                'category_id' => $dataBm->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataBm->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
