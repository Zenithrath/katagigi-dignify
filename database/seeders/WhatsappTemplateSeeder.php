<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class WhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'reminder_h1',
                'category' => 'reminder',
                'body' => 'Halo {nama}, pengingat jadwal kontrol gigi di {klinik} pada {tanggal} pukul {jam} dengan {dokter}. Balas YA untuk konfirmasi. Terima kasih.',
            ],
            [
                'name' => 'kontrol_lanjutan',
                'category' => 'reminder',
                'body' => 'Halo {nama}, {klinik} mengingatkan jadwal kontrol lanjutan pada {tanggal} pukul {jam}. Sampai jumpa!',
            ],
        ];

        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->updateOrInsert(
                ['name' => $template['name']],
                [
                    'id' => DB::table('whatsapp_templates')->where('name', $template['name'])->value('id') ?? Uuid::uuid4()->toString(),
                    'category' => $template['category'],
                    'body' => $template['body'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
