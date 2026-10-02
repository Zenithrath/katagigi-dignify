<?php

namespace Tests\Unit;

use App\Services\Payroll\AssistantOvertimeCalculator as Calc;
use PHPUnit\Framework\TestCase;

/**
 * Aturan lembur asisten: Rp15.000/jam, 45 menit = 1 jam.
 */
class AssistantOvertimeCalculatorTest extends TestCase
{
    public function test_normal_day_overtime_after_scheduled_end(): void
    {
        // Pulang 20:40, seharusnya 20:00 => 40 menit => 0 jam.
        $this->assertSame(40, Calc::overtimeMinutes('08:00', '20:40', '20:00', false));
        $this->assertSame(0, Calc::overtimeHours(40));
    }

    public function test_forty_five_minutes_counts_as_one_hour(): void
    {
        $this->assertSame(45, Calc::overtimeMinutes('08:00', '20:45', '20:00', false));
        $this->assertSame(1, Calc::overtimeHours(45));
        $this->assertSame(15000, Calc::overtimePay(1, 15000));
    }

    public function test_longer_overtime_rounds_with_grace(): void
    {
        // 104 menit => 1 jam; 105 menit => 2 jam.
        $this->assertSame(1, Calc::overtimeHours(104));
        $this->assertSame(2, Calc::overtimeHours(105));
    }

    public function test_no_overtime_when_leaving_early(): void
    {
        $this->assertSame(0, Calc::overtimeMinutes('08:00', '19:30', '20:00', false));
    }

    public function test_holiday_counts_from_clock_in(): void
    {
        // Tanggal merah: masuk 08:00 pulang 14:00 => 360 menit => 6 jam.
        $this->assertSame(360, Calc::overtimeMinutes('08:00', '14:00', '20:00', true));
        $this->assertSame(6, Calc::overtimeHours(360));
        $this->assertSame(90000, Calc::overtimePay(6, 15000));
    }

    public function test_invalid_clock_range_yields_zero(): void
    {
        $this->assertSame(0, Calc::overtimeMinutes('20:00', '08:00', '20:00', false));
        $this->assertSame(0, Calc::overtimeHours(0));
        $this->assertSame(0, Calc::overtimeHours(-5));
    }
}
