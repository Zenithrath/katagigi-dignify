<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Visit;
use App\Services\Clinical\VisitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * F4-T1: konteks cabang (switch + penandaan + filter).
 */
class BranchContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_manajemen_can_manage_branches_admin_cannot(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->get(route('branches.index'))->assertForbidden();
        $this->actingAs($manajemen)->get(route('branches.index'))->assertOk();

        $this->actingAs($manajemen)->post(route('branches.store'), [
            'code' => 'CBG-02',
            'name' => 'Cabang Dua',
        ])->assertRedirect();
        $this->assertDatabaseHas('branches', ['code' => 'CBG-02']);
    }

    public function test_switch_scopes_queue_and_tags_new_visit(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $branch2 = DB::table('branches')->where('code', 'CBG-01')->first();
        DB::table('branches')->insert([
            'id' => (string) Str::uuid(),
            'code' => 'CBG-02',
            'org' => 'Klinik Kata Gigi',
            'name' => 'Cabang Dua',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $branch2Id = DB::table('branches')->where('code', 'CBG-02')->value('id');

        $visitA = Visit::factory()->create(['visit_date' => date('Y-m-d'), 'clinical_status' => Visit::STATUS_WAITING]);
        $visitB = Visit::factory()->create([
            'visit_date' => date('Y-m-d'),
            'clinical_status' => Visit::STATUS_WAITING,
            'branch_id' => $branch2Id,
        ]);

        // Tanpa switch: semua terlihat.
        $this->actingAs($admin)->get(route('workspace.index'))
            ->assertOk()
            ->assertSee($visitA->visit_number, false)
            ->assertSee($visitB->visit_number, false);

        // Switch ke CBG-02: hanya visit cabang itu.
        $this->actingAs($admin)->post(route('branch.switch', ['branch_id' => $branch2Id]))->assertRedirect();
        $this->actingAs($admin)->get(route('workspace.index'))
            ->assertOk()
            ->assertDontSee($visitA->visit_number, false)
            ->assertSee($visitB->visit_number, false);

        // Visit baru ditandai cabang aktif.
        $service = new VisitService;
        $appointment = Appointment::factory()->create([
            'patient_id' => $visitA->patient_id,
            'doctor_id' => $visitA->doctor_id,
        ]);
        $new = $service->createVisit([
            'patient_id' => $visitA->patient_id,
            'doctor_id' => $visitA->doctor_id,
            'appointment_id' => $appointment->id,
        ]);
        $this->assertEquals($branch2Id, $new->branch_id);
    }
}
