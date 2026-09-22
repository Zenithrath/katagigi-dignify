<?php

namespace App\Jobs;

use App\Models\Visit;
use App\Services\SatuSehat\SatuSehatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncVisitToSatuSehat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(public Visit $visit)
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('satusehat-visit-'.$this->visit->id))->releaseAfter(30),
        ];
    }

    public function handle(SatuSehatService $service): void
    {
        $service->syncVisit($this->visit->fresh());
    }

    public function failed(?Throwable $e): void
    {
        report($e);
    }
}
