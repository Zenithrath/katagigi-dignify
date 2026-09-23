<?php

namespace App\Console\Commands;

use App\Models\Visit;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Console\Command;

class SatuSehatSyncVisit extends Command
{
    protected $signature = 'satusehat:sync-visit {visit : ID visit SIGNED}';

    protected $description = 'Sinkronkan satu visit SIGNED ke SATUSEHAT (Patient → Encounter → Condition → Procedure).';

    public function handle(SatuSehatService $satusehat): int
    {
        $visit = Visit::find($this->argument('visit'));
        if (! $visit) {
            $this->error('Visit tidak ditemukan.');

            return self::FAILURE;
        }
        if (! $visit->isSigned()) {
            $this->error('Hanya visit SIGNED yang disinkronkan.');

            return self::FAILURE;
        }

        try {
            $summary = $satusehat->syncVisit($visit);
            $this->info("Sukses: {$summary['success']}, gagal: {$summary['failed']}, dilewati: {$summary['skipped']}.");

            return self::SUCCESS;
        } catch (\Throwable $th) {
            $this->error($th->getMessage());

            return self::FAILURE;
        }
    }
}
