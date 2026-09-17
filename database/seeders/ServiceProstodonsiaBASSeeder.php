<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceProstodonsiaBASSeeder extends Seeder
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
        $plb = DB::table('categories')->where('name', 'Prostodonsia Lab BAS')->first();
        $dataPlb = (object) [
            'id' => $plb->id,
            'code' => $plb->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Akrilik BAS 1',
                'category_id' => $dataPlb->id,
                'description' => 'Akrilik gigi pertama (per rahang) : gigi tiruan bahan akrilik',
                'lower_price' => 900000,
                'upper_price' => 900000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Akrilik BAS 2',
                'category_id' => $dataPlb->id,
                'description' => 'Akrilik gigi selanjutnya (gigi tiruan bahan akrilik)',
                'lower_price' => 150000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Valpplast BAS 1',
                'category_id' => $dataPlb->id,
                'description' => 'Valplast gigi pertama (per rahang)',
                'lower_price' => 1200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Valpplast BAS 2',
                'category_id' => $dataPlb->id,
                'description' => 'Valplast gigi selanjutnya',
                'lower_price' => 150000,
                'upper_price' => 150000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture BAS 1',
                'category_id' => $dataPlb->id,
                'description' => 'Full denture 1 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 1900000,
                'upper_price' => 1900000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture BAS 2',
                'category_id' => $dataPlb->id,
                'description' => 'Full denture 2 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 3700000,
                'upper_price' => 4200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi GT Akrilik BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Reparasi GT Akrilik BAS',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Relining BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Relining / Rebasing (perbaikan gigi tiruan yang longgar atau kurang pas saat dipakai)',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Valplast BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Reparasi/perbaikan valplast 1 gigi pertama : perbaikan gigi palsu bahan valplast',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Akrilik BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Reparasi akrilik 1 gigi pertama : perbaikan gigi palsu bahan akrilik',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Individual Tray 1 Rahang BAS',
                'category_id' => $dataPlb->id,
                'description' => 'individual tray 1 rahang : sendok cetak sesuai dengan bentuk rahang pasien yang dibuat untuk proses pembuatan gigi tiruan',
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Night Guard BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Night guard terapi bruxism (alat yang dipasang saat malam hari untuk melindungi gigi dari gesekan bruxism. Bruxism: kebiasaan menggerakkan gigi rahang atas dan bawah sampai berbunyi kerot, kerot berisik dan membuat lapisan gigi menjadi rusak)',
                'lower_price' => 650000,
                'upper_price' => 650000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Klamer BAS',
                'category_id' => $dataPlb->id,
                'description' => 'Klamer (cantolan gigi tiruan ke gigi)',
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataPlb->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
