<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceProstodonsiaKlinikSeeder extends Seeder
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
        $pk = DB::table('categories')->where('name', 'Prostodonsia Klinik')->first();
        $dataPk = (object) [
            'id' => $pk->id,
            'code' => $pk->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bongkar Gigi Palsu Kortugi Simple',
                'category_id' => $dataPk->id,
                'description' => 'Bongkar gigi palsu kortugi simple (korban tukang gigi)',
                'lower_price' => 200000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bongkar Gigi Palsu Kortugi Kompleks',
                'category_id' => $dataPk->id,
                'description' => 'Bongkar gigi palsu kortugi kompleks (korban tukang gigi)',
                'lower_price' => 300000,
                'upper_price' => 400000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bridge Fiber Komposit 1',
                'category_id' => $dataPk->id,
                'description' => 'Bridge Fiber Komposit Butuh fiber  > 4mm (gigi tiruan bahan fiber & komposit)',
                'lower_price' => 1200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pasak Fiber + Luting',
                'category_id' => $dataPk->id,
                'description' => 'Bridge Fiber Komposit Butuh fiber  < 4mm  (gigi tiruan bahan fiber & komposit)',
                'lower_price' => 850000,
                'upper_price' => 850000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pasak Fiber + Luting',
                'category_id' => $dataPk->id,
                'description' => 'Pasak fiber + luting : mengisi saluran akar',
                'lower_price' => 2500000,
                'upper_price' => 2500000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataPk->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
