<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll asisten (perawat)
    |--------------------------------------------------------------------------
    | overtime_rate      : rupiah per jam lembur.
    | rounding_grace     : menit toleransi pembulatan ke atas. 45 menit
    |                      lembur dihitung 1 jam  => jam = floor((m+15)/60).
    | default_scheduled_end : jam pulang normal bila tidak diinput (H:i).
    */
    'overtime_rate' => (int) env('ASSISTANT_OVERTIME_RATE', 15000),
    'rounding_grace_minutes' => (int) env('ASSISTANT_OVERTIME_GRACE', 15),
    'default_scheduled_end' => env('ASSISTANT_SCHEDULED_END', '20:00'),
];
