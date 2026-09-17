<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceProstodonsiaDeltaSeeder extends Seeder
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
        $delta = DB::table('categories')->where('name', 'Prostodonsia Lab Delta')->first();
        $dataDelta = (object) [
            'id' => $delta->id,
            'code' => $delta->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Onlay/Inlay PFM DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Onlay/inlay PFM : tambalan gigi berlubang lebar dengan bahan logam dan porcelen yang sewarna gigi agar gigi belakang lebih kuat untuk mengunyah',
                'lower_price' => 1600000,
                'upper_price' => 1600000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Onlay/Inlay Metal DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Onlay/inlay metal : tambalan gigi berlubang lebar dengan bahan logam berwarna abu-abu agar gigi belakang lebih kuat untuk mengunyah',
                'lower_price' => 1200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Crown/Bridge PFM DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Crown/bridge PFM/unit : selubung gigi yang sewarna gigi dengan bahan PFM',
                'lower_price' => 1750000,
                'upper_price' => 1750000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Crown/Bridge E-Max DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Crown/bridge E-Max/unit : selubung gigi yang sewarna gigi dengan bahan E-max',
                'lower_price' => 2500000,
                'upper_price' => 2500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bridge Maryland PFM DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Bridge maryland PFM/unit : gigi tiruan permanen model maryland, lebih cepat dan hemat)',
                'lower_price' => 2500000,
                'upper_price' => 2500000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pasak Tuang DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Pasak tuang : mengisi saluran akar dengan logam',
                'lower_price' => 1800000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Akrilik DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Akrilik gigi pertama (per rahang) : gigi tiruan bahan akrilik',
                'lower_price' => 1200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Valplast Delta',
                'category_id' => $dataDelta->id,
                'description' => 'Valplast gigi pertama (per rahang)',
                'lower_price' => 1800000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture DELTA 1',
                'category_id' => $dataDelta->id,
                'description' => 'Full denture 1 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 3000000,
                'upper_price' => 3000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture DELTA 2',
                'category_id' => $dataDelta->id,
                'description' => 'Full denture 2 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 5800000,
                'upper_price' => 5800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi GT Akrilik DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Reparasi GT plat akrilik patah (perbaikan gigi tiruan bahan akrilik yang patah)',
                'lower_price' => 900000,
                'upper_price' => 900000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Valplast DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Reparasi/perbaikan valplast 1 gigi pertama : perbaikan gigi palsu bahan valplast',
                'lower_price' => 1800000,
                'upper_price' => 1800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Akrilik DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Reparasi akrilik 1 gigi pertama : perbaikan gigi palsu bahan akrilik',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Night Guard DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Night guard terapi bruxism (alat yang dipasang saat malam hari untuk melindungi gigi dari gesekan bruxism. Bruxism: kebiasaan menggerakkan gigi rahang atas dan bawah sampai berbunyi kerot, kerot berisik dan membuat lapisan gigi menjadi rusak)',
                'lower_price' => 1300000,
                'upper_price' => 1300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Klamer DELTA',
                'category_id' => $dataDelta->id,
                'description' => 'Klamer (cantolan gigi tiruan ke gigi)',
                'lower_price' => 150000,
                'upper_price' => 150000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataDelta->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
