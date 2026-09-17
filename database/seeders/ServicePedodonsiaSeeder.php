<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServicePedodonsiaSeeder extends Seeder
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
        $pdo = DB::table('categories')->where('name', 'Pedodonsia')->first();
        $dataPdo = (object) [
            'id' => $pdo->id,
            'code' => $pdo->code,
        ];

        // 'code' => $this->serviceService->generateServiceCode($dataUmum->code),

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi Anak CE',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cabut Gigi Anak TAF',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal Sementara',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 650000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal Permanen Anak',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pulpotomi',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pulpektomi',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Open Bur',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Obturasi Saluran Akar',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Space Maintaner Anak',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 650000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Incline Bite Plane Anterior Anak',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Fissure Sealant Anak',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Topikal Aplikasi Fluor',
                'category_id' => $dataPdo->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataPdo->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
