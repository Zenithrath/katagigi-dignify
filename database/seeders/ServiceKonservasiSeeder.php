<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceKonservasiSeeder extends Seeder
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
        $kv = DB::table('categories')->where('name', 'Konservasi')->first();
        $dataKv = (object) [
            'id' => $kv->id,
            'code' => $kv->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Open Bur / TS+Obat',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Open Bur + Anastesi',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal GIC',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal ZnPO4 + GIC',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 250000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal Komposit',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Veneer',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 400000,
                'upper_price' => 500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Veneer Indirect Lab',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 2000000,
                'upper_price' => 2200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Sandwich',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 250000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pulcapping',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Preparaso',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Tambal Sementara PSA',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 1500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Ekstipasi + Preparasi PSA',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 150000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Preparasi Orivice + TS PSA',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pengisian PSA Tunggal',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pengsian PSA Ganda',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 250000,
                'upper_price' => 400000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Topikal Aplikasi Fluor',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 50000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pasak Fiber + Luting',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 200000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Insisi & Drainase Abses',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Desentisasi',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 100000,
                'upper_price' => 300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bleaching',
                'category_id' => $dataKv->id,
                'description' => null,
                'lower_price' => 599000,
                'upper_price' => 1200000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataKv->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
