<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->actingAs($user)->get('/salaries')->assertOk();
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

    public function test_admin_cannot_cancel_directly_but_can_propose(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        $this->assertTrue($admin->can('request cancellation'));
        $this->assertFalse($admin->can('approve cancellation'));
        $this->assertTrue($manajemen->can('approve cancellation'));
    }
}
