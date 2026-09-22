<?php

namespace App\Services\Payroll;

/**
 * Aturan lembur asisten (perawat) — pure function, mudah diuji.
 *
 * - Hari biasa : lembur = max(0, pulang - jam pulang seharusnya).
 * - Tanggal merah : seluruh jam dari masuk dihitung lembur.
 * - Pembulatan : 45 menit ke atas = 1 jam  => jam = floor((menit+15)/60).
 * - Upah : jam_lembur x tarif per jam.
 */
class AssistantOvertimeCalculator
{
    public static function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return $h * 60 + $m;
    }

    /**
     * Menit lembur untuk satu hari kerja.
     */
    public static function overtimeMinutes(string $clockIn, string $clockOut, string $scheduledEnd, bool $isHoliday): int
    {
        $in = static::toMinutes($clockIn);
        $out = static::toMinutes($clockOut);

        if ($out <= $in) {
            return 0;
        }

        if ($isHoliday) {
            return $out - $in;
        }

        return max(0, $out - static::toMinutes($scheduledEnd));
    }

    /**
     * Menit lembur -> jam tagih. Di bawah 45 menit = 0 jam.
     */
    public static function overtimeHours(int $minutes, int $graceMinutes = 15): int
    {
        if ($minutes <= 0) {
            return 0;
        }

        return intdiv($minutes + $graceMinutes, 60);
    }

    public static function overtimePay(int $hours, int $ratePerHour): int
    {
        return max(0, $hours) * max(0, $ratePerHour);
    }
}
