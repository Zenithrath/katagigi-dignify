<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClinicSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manajemen_can_open_all_main_pages(): void
    {
        $user = User::where('email', 'manajemen@gmail.com')->first();

        $pages = [
            'dashboard',
            'admins.index', 'doctors.index', 'nurses.index',
            'patients.index', 'schedules.index', 'appointments.index',
            'services.index', 'categories.index', 'medical-records.index',
            'transactions.index', 'incomes.index', 'installments.index',
        ];

        foreach ($pages as $route) {
            $response = $this->actingAs($user)->get(route($route));
            $this->assertEquals(
                200, $response->status(),
                "Route [$route] returned ".$response->status()
            );
        }

        // D-06g: salaries kini route bernama.
        $this->actingAs($user)->get(route('salaries.index'))->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('patients.index'))->assertRedirect(route('login'));
    }

    public function test_diagnosis_search_api_works(): void
    {
        $user = User::where('email', 'doctor@gmail.com')->first();

        $response = $this->actingAs($user)->getJson('/api/diagnosis-codes?q=gigi+berlubang');

        $response->assertOk()->assertJsonPath('data.0.code', 'K02.1');
    }

    /**
     * D-06d/e: seeder tarif prostodonsia wajib masuk DB (dulu rollback
     * diam-diam karena bug koma + kategori lab tak aktif).
     */
    public function test_prosthodontics_seed_data_integrity(): void
    {
        $pro = DB::table('categories')->where('name', 'Prostodonsia')->first();
        $this->assertNotNull($pro);
        $this->assertGreaterThan(0, DB::table('services')->where('category_id', $pro->id)->count());

        $akrilik = DB::table('services')->where('name', 'Reparasi Akrilik')->first();
        $this->assertNotNull($akrilik);
        $this->assertEquals(1600000, (int) $akrilik->upper_price);

        foreach (['Prostodonsia Lab BAS', 'Prostodonsia Klinik', 'Prostodonsia Lab Afif', 'Prostodonsia Lab Delta'] as $lab) {
            $category = DB::table('categories')->where('name', $lab)->first();
            $this->assertNotNull($category, "Kategori [$lab] harus ada");
            $this->assertGreaterThan(0, DB::table('services')->where('category_id', $category->id)->count(), "Layanan [$lab] harus ada");
        }
    }

    public function test_admin_cannot_cancel_directly_but_can_propose(): void
    {        $admin = User::where('email', 'admin@gmail.com')->first();
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        $this->assertTrue($admin->can('request cancellation'));
        $this->assertFalse($admin->can('approve cancellation'));
        $this->assertTrue($manajemen->can('approve cancellation'));
    }
}
