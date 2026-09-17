<?php

namespace Database\Seeders;

use App\Services\General\ServiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ServiceProstodonsiaAfifSeeder extends Seeder
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
        $pla = DB::table('categories')->where('name', 'Prostodonsia Lab Afif')->first();
        $dataPla = (object) [
            'id' => $pla->id,
            'code' => $pla->code,
        ];

        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Onlay/Inlay PFM AFIF',
                'category_id' => $dataPla->id,
                'description' => 'Onlay/inlay PFM : tambalan gigi berlubang lebar dengan bahan logam dan porcelen yang sewarna gigi agar gigi belakang lebih kuat untuk mengunyah',
                'lower_price' => 1200000,
                'upper_price' => 1200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Onlay/Inlay Metal AFIF',
                'category_id' => $dataPla->id,
                'description' => 'Onlay/inlay metal : tambalan gigi berlubang lebar dengan bahan logam berwarna abu-abu agar gigi belakang lebih kuat untuk mengunyah',
                'lower_price' => 1000000,
                'upper_price' => 1000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Crown/Bridge PFM Afif',
                'category_id' => $dataPla->id,
                'description' => 'Crown/bridge PFM/unit : selubung gigi yang sewarna gigi dengan bahan PFM',
                'lower_price' => 1350000,
                'upper_price' => 1350000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Akrilik AFIF 1',
                'category_id' => $dataPla->id,
                'description' => 'Akrilik gigi pertama (per rahang) : gigi tiruan bahan akrilik',
                'lower_price' => 1050000,
                'upper_price' => 1050000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Akrilik AFIF 2',
                'category_id' => $dataPla->id,
                'description' => 'Akrilik gigi selanjutnya (gigi tiruan bahan akrilik)',
                'lower_price' => 200000,
                'upper_price' => 200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Valplast AFIF 1',
                'category_id' => $dataPla->id,
                'description' => 'Valplast gigi pertama (per rahang)',
                'lower_price' => 1300000,
                'upper_price' => 1300000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Valplast AFIF 2',
                'category_id' => $dataPla->id,
                'description' => 'Valplast gigi selanjutnya',
                'lower_price' => 250000,
                'upper_price' => 250000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture AFIF 1',
                'category_id' => $dataPla->id,
                'description' => 'Full denture 1 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 2200000,
                'upper_price' => 2200000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Full Denture AFIF 2',
                'category_id' => $dataPla->id,
                'description' => 'Full denture 2 rahang (gigi tiruan lengkap mengganti semua gigi. Disarankan pakai bahan akrilik untuk menghindari resorbsi ridge berlebihan)',
                'lower_price' => 4000000,
                'upper_price' => 4000000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Reparasi Akrilik AFIF',
                'category_id' => $dataPla->id,
                'description' => 'Reparasi akrilik 1 gigi pertama : perbaikan gigi palsu bahan akrilik',
                'lower_price' => 800000,
                'upper_price' => 800000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Individual Tray 1 Rahang AFIF',
                'category_id' => $dataPla->id,
                'description' => 'individual tray 1 rahang : sendok cetak sesuai dengan bentuk rahang pasien yang dibuat untuk proses pembuatan gigi tiruan',
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Klamer AFIF',
                'category_id' => $dataPla->id,
                'description' => 'Klamer (cantolan gigi tiruan ke gigi)',
                'lower_price' => 100000,
                'upper_price' => 100000,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($data as $key => $item) {
                $item['code'] = $this->serviceService->generateServiceCode($dataPla->code);

                DB::table('services')->insert($item);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
