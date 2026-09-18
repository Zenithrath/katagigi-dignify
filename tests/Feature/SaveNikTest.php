<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaveNikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_store_patient_with_nik_fields(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->post(route('patients.store'), [
            'name' => 'Pasien NIK',
            'phone' => '081234567890',
            'gender' => 'MALE',
            'birthdate' => '1990-05-17',
            'birth_place' => 'Banjarmasin',
            'nik' => '6371011705900001',
            'ihs_id' => 'P001234567',
            'satusehat_consent' => '1',
            'street' => 'Jl NIK',
            'village' => 'V',
            'district' => 'D',
            'regency' => 'R',
            'province' => 'P',
            'zip_code' => '70111',
            'tonarigumi' => '01/02',
        ])->assertRedirect(route('patients.index'));

        $this->assertDatabaseHas('patients', [
            'name' => 'Pasien NIK',
            'birth_place' => 'Banjarmasin',
            'nik' => '6371011705900001',
            'ihs_id' => 'P001234567',
            'satusehat_consent' => true,
        ]);
    }

    public function test_patient_store_rejects_invalid_nik(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->post(route('patients.store'), [
            'name' => 'Pasien NIK Rusak',
            'phone' => '081234567891',
            'nik' => '123',
        ])->assertSessionHasErrors('nik');

        $this->assertDatabaseMissing('patients', ['name' => 'Pasien NIK Rusak']);
    }

    public function test_patient_store_rejects_duplicate_nik(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $existingNik = DB::table('patients')->whereNotNull('nik')->value('nik');
        $this->assertNotNull($existingNik, 'Seeder harus punya pasien ber-NIK');

        $this->actingAs($admin)->post(route('patients.store'), [
            'name' => 'Pasien Duplikat',
            'phone' => '081234567892',
            'nik' => $existingNik,
        ])->assertSessionHasErrors('nik');
    }

    public function test_patient_update_keeps_own_nik(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $patient = DB::table('patients')->whereNotNull('nik')->first();
        $this->assertNotNull($patient);

        $this->actingAs($admin)->put(route('patients.update', $patient->id), [
            'name' => $patient->name,
            'phone' => '081234567893',
            'nik' => $patient->nik,
        ])->assertRedirect(route('patients.index'));
    }

    public function test_manajemen_can_store_doctor_with_ihs_id(): void
    {
        $manajemen = User::where('email', 'manajemen@gmail.com')->first();

        $this->actingAs($manajemen)->post(route('doctors.store'), [
            'name' => 'Dr IHS',
            'email' => 'drihs@mail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nipp' => 'IHS001',
            'ihs_id' => 'D001234567',
            'street' => 'Jl Dokter',
            'village' => 'V',
            'district' => 'D',
            'regency' => 'R',
            'province' => 'P',
            'zip_code' => '70111',
            'tonarigumi' => '01/02',
        ])->assertRedirect(route('doctors.index'));

        $this->assertDatabaseHas('doctors', [
            'nipp' => 'IHS001',
            'ihs_id' => 'D001234567',
        ]);
    }

    public function test_forms_and_detail_expose_satusehat_fields(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();

        $this->actingAs($admin)->get(route('patients.create'))
            ->assertOk()
            ->assertSee('NIK (16 digit)', false)
            ->assertSee('satusehat_consent', false)
            ->assertSee('ID IHS (SATUSEHAT)', false);

        $patient = DB::table('patients')->first();
        $this->actingAs($admin)->get(route('patients.show', $patient->id))
            ->assertOk()
            ->assertSee('Kesiapan SATUSEHAT', false);

        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $this->actingAs($doctor)->get(route('medical-records.create'))
            ->assertOk()
            ->assertSee('Diagnosis Penyakit (ICD-10)', false)
            ->assertSee('Tindakan / Prosedur (ICD-9', false);
    }
}
