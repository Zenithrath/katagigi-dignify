<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceProstodonsiaSeeder extends Seeder
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
        $pro = DB::table('categories')->where('name', 'Prostodonsia')->first();
        $dataPro = (object) [
            'id' => $pro->id,
            'code' => $pro->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bongkar Gigi Palsu',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Bridge Fiber Komposit',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 500000,
                'upper_price' => 4000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Akrilik',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 900000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Valplast',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 999000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Full Denture',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 2000000,
                'upper_price' => 5800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Thermosens',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 1350000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Onlay/Inlay PFM',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 1600000,
                'upper_price' => 2000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Onlay/Inlay Metal',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 1200000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Crown/Bridge PFM',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 1750000,
                'upper_price' => 2000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Crown/Bridge E-Max',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 2500000,
                'upper_price' => 2750000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Gigi Palsu Bridge Maryland PFM',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 2500000,
                'upper_price' => 2750000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi GT Akrilik',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 800000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Valplast',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 800000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Akrilik',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 800000,
                'upper_price' => 1600000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Relining',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 800000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Individual Tray',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Night Guard',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 650000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Klamer',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambahan Gigi Palsu Akrilik',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambahan Gigi Palsu Valplast',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambahan Gigi Palsu Thermosens',
                'category_id' => $dataPro->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 250000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataPro->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
