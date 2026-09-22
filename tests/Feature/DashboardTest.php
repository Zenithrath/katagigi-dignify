<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Dashboard per role + regresi __PHP_Incomplete_Class:
 * store cache "database" (dipakai di dev/prod) meng-unserialize dengan
 * allowed_classes terbatas, sehingga overview harus berupa array serialize-safe.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function verifiedUser(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_admin_dashboard_renders_with_database_cache(): void
    {
        config(['cache.default' => 'database']);
        Cache::forget('dashboard:admin-overview');

        $this->actingAs($this->verifiedUser('manajemen@gmail.com'))
            ->get(route('dashboard'))
            ->assertOk();

        // Hasil overview tersimpan sebagai array dan terbaca utuh kembali
        // (bukan __PHP_Incomplete_Class).
        $cached = Cache::get('dashboard:admin-overview');
        $this->assertIsArray($cached);
        $this->assertArrayHasKey('transactions', $cached);
    }

    public function test_nurse_dashboard_renders_with_database_cache(): void
    {
        config(['cache.default' => 'database']);
        Cache::forget('dashboard:doctor-overview:all');

        $this->actingAs($this->verifiedUser('nurse@gmail.com'))
            ->get(route('dashboard'))
            ->assertOk();

        $cached = Cache::get('dashboard:doctor-overview:all');
        $this->assertIsArray($cached);
    }

    public function test_doctor_dashboard_renders(): void
    {
        $this->actingAs($this->verifiedUser('doctor@gmail.com'))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_admin_dashboard_renders_widget_partials(): void
    {
        config(['cache.default' => 'database']);
        Cache::forget('dashboard:admin-overview');

        $response = $this->actingAs($this->verifiedUser('manajemen@gmail.com'))
            ->get(route('dashboard'));
        $response->assertOk();
        $this->assertStringContainsString('data-widget="kpi-row"', $response->getContent());
        $this->assertStringContainsString('data-widget="patients-incomplete"', $response->getContent());
    }

    public function test_doctor_overview_scopes_revenue_to_own_transactions(): void
    {
        config(['cache.default' => 'database']);

        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['doctor_id' => $doctorA->user_id]);

        $makeTrx = function (Doctor $doctor) use ($patient, $appointment) {
            DB::table('transactions')->insert([
                'id' => (string) Str::uuid(), 'sequence' => random_int(1, 999999),
                'patient_id' => $patient->id, 'patient_name' => $patient->name, 'patient_code' => $patient->code,
                'doctor_id' => $doctor->user_id, 'doctor_name' => 'T',
                'appointment_id' => $appointment->id,
                'appointment_datetime' => now()->format('Y-m-d H:i:s'),
                'services' => '[]', 'price' => 100000, 'discount' => 0, 'billing' => 150000,
                'payment_method' => 'CASH', 'created_at' => now(), 'updated_at' => now(),
            ]);
        };

        $makeTrx($doctorA);
        $makeTrx($doctorB);

        $overviewA = app(DashboardService::class)->getDoctorDataOverview($doctorA->user_id);
        $this->assertSame(150000.0, $overviewA['revenue']);

        // Tanpa filter dokter (kasus perawat) juga menghitung transaksi dokter B
        // (DB berisi data seeder lain, jadi asersi relatif, bukan angka pasti).
        $overviewAll = app(DashboardService::class)->getDoctorDataOverview(null);
        $this->assertGreaterThan(150000.0, $overviewAll['revenue']);
    }
}
