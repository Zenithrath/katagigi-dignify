<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceUmumSeeder extends Seeder
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
        $umum = DB::table('categories')->where('name', 'Umum')->first();
        $dataUmum = (object) [
            'id' => $umum->id,
            'code' => $umum->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Konsultasi dan Checkup Gigi',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 25000,
                'upper_price' => 100000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Scaling Karang Gigi ',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 99000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Dental Spa',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 199000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Splinting Wire',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 350000,
                'upper_price' => 500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gingivektomi',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cetak Alginat 1 Rahang',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Cetak Alginat 2 Rahang',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Rontgen Periapical',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 75000,
                'upper_price' => 75000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Sikat Lidah',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 5000,
                'upper_price' => 5000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Sikat Gigi Ortho',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 20000,
                'upper_price' => 20000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Ortho Kit Set',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 40000,
                'upper_price' => 40000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Dental Floss Stick',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 5000,
                'upper_price' => 5000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Wax Ortho',
                'category_id' => $dataUmum->id,
                'description' => null,
                'lower_price' => 5000,
                'upper_price' => 5000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataUmum->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
