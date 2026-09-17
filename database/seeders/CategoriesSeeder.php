<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => Uuid::uuid4(),
                'name' => 'Umum',
                'code' => 'UM',
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Pedodonsia',
                'code' => 'PDO',
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Bedah Mulut',
                'code' => 'BM',
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Konservasi',
                'code' => 'KV',
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Ortodonsia',
                'code' => 'ORTO',
            ],
            [
                'id' => Uuid::uuid4(),
                'name' => 'Prostodonsia',
                'code' => 'PRO',
            ],
            // [
            //     'id' => Uuid::uuid4(),
            //     'name' => 'Prostodonsia Lab BAS',
            //     'code' => 'PROSTO BAS',
            // ],
            // [
            //     'id' => Uuid::uuid4(),
            //     'name' => 'Prostodonsia Klinik',
            //     'code' => 'PROSTO KLINIK',
            // ],
            // [
            //     'id' => Uuid::uuid4(),
            //     'name' => 'Prostodonsia Lab Afif',
            //     'code' => 'PROSTO AFIF',
            // ],
            // [
            //     'id' => Uuid::uuid4(),
            //     'name' => 'Prostodonsia Lab Delta',
            //     'code' => 'PROSTO DELTA',
            // ],
        ];

        DB::beginTransaction();
        try {
            DB::table('categories')->insert($data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
