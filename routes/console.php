<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Fase 4 T3: pengingat WhatsApp H-1 untuk appointment terkonfirmasi besok.
// Sekali sehari jam 07:00, satu server saja bila pakai multiple server.
Schedule::command('wa:remind-h1')->dailyAt('07:00')->onOneServer()->runInBackground();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
