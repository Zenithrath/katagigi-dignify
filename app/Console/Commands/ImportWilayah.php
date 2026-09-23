<?php

namespace App\Console\Commands;

use App\Models\RegionCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4.1: impor penuh wilayah Kemendagri dari CSV
 * (format: kode,nama,parent — level diturunkan dari panjang kode).
 *
 * Sumber CSV yang lazim dipakai: export Kemendagri via kodepos/dukcapil.
 * Pemakaian: php artisan satusehat:import-wilayah database/data/wilayah.csv
 */
class ImportWilayah extends Command
{
    protected $signature = 'satusehat:import-wilayah {path : Path file CSV (kode,nama,parent)}';

    protected $description = 'Impor master wilayah Kemendagri (provinsi/kota/kecamatan/kelurahan) dari CSV';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->error('Gagal membuka file.');

            return self::FAILURE;
        }

        $count = 0;
        $skipped = 0;
        DB::transaction(function () use ($handle, &$count, &$skipped) {
            $line = 0;
            while (($row = fgetcsv($handle)) !== false) {
                $line++;
                // Lewati header bila ada (baris pertama tidak berupa kode).
                if ($line === 1 && ! preg_match('/^\d+$/', trim($row[0] ?? ''))) {
                    continue;
                }
                if (count($row) < 2 || ! preg_match('/^\d{2,10}$/', trim($row[0]))) {
                    $skipped++;

                    continue;
                }

                $code = trim($row[0]);
                $name = trim($row[1]);
                $parent = trim($row[2] ?? '') ?: null;

                RegionCode::updateOrCreate(['code' => $code], [
                    'name' => $name,
                    'level' => match (strlen($code)) {
                        2 => RegionCode::LEVEL_PROVINCE,
                        4 => RegionCode::LEVEL_CITY,
                        6 => RegionCode::LEVEL_DISTRICT,
                        default => RegionCode::LEVEL_VILLAGE,
                    },
                    'parent_code' => $parent,
                    'is_active' => true,
                ]);
                $count++;
            }
        });
        fclose($handle);

        $this->info("Import selesai: {$count} baris dimasukkan/diperbarui, {$skipped} dilewati.");

        return self::SUCCESS;
    }
}
