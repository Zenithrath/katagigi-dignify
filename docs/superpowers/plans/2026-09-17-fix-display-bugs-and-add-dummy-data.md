# Fix Display Bugs & Add Dummy Data Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix display bugs that occur after form submissions and create 10+ dummy records for each data section (patients, schedules, appointments, transactions, medical records).

**Architecture:** Fix 3 known bugs (alert components + Schedule model), then create Eloquent factories + seeders for all domain models. Seed via `php artisan db:seed`.

**Tech Stack:** Laravel 13, Eloquent Factories, Seeder classes, Blade components

**Spec:** N/A (bug fixes + seed data — no design doc needed)

## Global Constraints

- PHP 8.3+, Laravel 13
- UUID primary keys on most models (Str::uuid())
- Must maintain referential integrity (patients → appointments → transactions → medical records)
- All seed data uses realistic Indonesian dental clinic data

---

## Task 1: Fix Alert Component Syntax Bug

**Files:**
- Modify: `resources/views/components/alerts/success.blade.php:12`
- Modify: `resources/views/components/alerts/failed.blade.php:11`

**Problem:** Both alert components use `$attributes['message'] }}` instead of `{{ $attributes['message'] }}`. The opening `{{ ` is missing, so flash message text never renders.

- [ ] **Step 1: Read the broken files**

```bash
cat resources/views/components/alerts/success.blade.php
cat resources/views/components/alerts/failed.blade.php
```

- [ ] **Step 2: Fix success alert**

In `resources/views/components/alerts/success.blade.php`, find the `<h3>` line with `$attributes['message'] }}` and change it to `{{ $attributes['message'] }}`.

- [ ] **Step 3: Fix failed alert**

In `resources/views/components/alerts/failed.blade.php`, find the `<h3>` line with `$attributes['message'] }}` and change it to `{{ $attributes['message'] }}`.

- [ ] **Step 4: Verify fix**

```bash
php artisan view:clear
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/alerts/success.blade.php resources/views/components/alerts/failed.blade.php
git commit -m "fix: repair broken Blade syntax in flash alert components"
```

---

## Task 2: Fix Schedule Model Missing Import

**Files:**
- Modify: `app/Models/Schedule.php`

**Problem:** `Schedule` model uses `HasMany` in relationship methods but doesn't import `Illuminate\Database\Eloquent\Relations\HasMany`. This causes fatal errors when relationships are accessed.

- [ ] **Step 1: Read the file**

```bash
cat app/Models/Schedule.php
```

- [ ] **Step 2: Add missing import**

Add `use Illuminate\Database\Eloquent\Relations\HasMany;` to the imports section.

- [ ] **Step 3: Commit**

```bash
git add app/Models/Schedule.php
git commit -m "fix: add missing HasMany import to Schedule model"
```

---

## Task 3: Create Patient Factory

**Files:**
- Create: `database/factories/PatientFactory.php`

**Fields to generate:**
- `id`: Str::uuid()
- `name`: Fake Indonesian name
- `code`: Unique patient code (e.g., PSG-0001)
- `email`: Fake email
- `phone`: Fake Indonesian phone number
- `birthdate`: Fake past date
- `birth_place`: Fake Indonesian city
- `nik`: 16-digit fake NIK
- `ihs_id`: Fake IHS ID
- `religion`: Random from enum (islam, kristen, katolik, hindu, buddha, konghucu)
- `gender`: Random (male, female)
- `picture`: null

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $name = $gender === 'male'
            ? $this->faker->maleName
            : $this->faker->femaleName;

        return [
            'id' => Str::uuid(),
            'name' => $name,
            'code' => 'PSG-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'email' => $this->faker->safeEmail,
            'phone' => '08' . $this->faker->numerify('##########'),
            'birthdate' => $this->faker->dateTimeBetween('-60 years', '-5 years'),
            'birth_place' => $this->faker->city,
            'nik' => $this->faker->numerify('################'),
            'ihs_id' => 'IHS-' . $this->faker->numerify('##########'),
            'religion' => $this->faker->randomElement(['islam', 'kristen', 'katolik', 'hindu', 'buddha', 'konghucu']),
            'gender' => $gender,
            'picture' => null,
            'sosmed' => null,
            'satusehat_consent' => $this->faker->boolean(80),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/PatientFactory.php
git commit -m "feat: add Patient factory with Indonesian dental clinic data"
```

---

## Task 4: Create Schedule Factory

**Files:**
- Create: `database/factories/ScheduleFactory.php`

**Fields to generate:**
- `id`: Str::uuid()
- `doctor_id`: From existing Doctor records
- `day`: Day of week (monday-saturday)
- `time_start`: Morning or afternoon slot
- `time_end`: 1-2 hours after start

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $startHour = $this->faker->randomElement([8, 9, 10, 13, 14]);
        $duration = $this->faker->randomElement([1, 2]);

        return [
            'id' => Str::uuid(),
            'doctor_id' => Doctor::inRandomOrder()->first()?->user_id ?? Doctor::factory(),
            'day' => $this->faker->randomElement($days),
            'time_start' => sprintf('%02d:00:00', $startHour),
            'time_end' => sprintf('%02d:00:00', $startHour + $duration),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/ScheduleFactory.php
git commit -m "feat: add Schedule factory"
```

---

## Task 5: Create Appointment Factory

**Files:**
- Create: `database/factories/AppointmentFactory.php`

**Fields to generate:**
- `id`: Str::uuid()
- `patient_id`: From existing Patient records
- `patient_name`: Denormalized from patient
- `doctor_id`: From existing Doctor records
- `doctor_name`: Denormalized from doctor
- `schedule_id`: From existing Schedule records
- `date`: Fake date in 2026
- `time_start`/`time_end`: From schedule
- `status`: Random status

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first();
        $doctor = Doctor::inRandomOrder()->first();
        $schedule = Schedule::where('doctor_id', $doctor?->user_id)->inRandomOrder()->first();

        return [
            'id' => Str::uuid(),
            'patient_id' => $patient?->id ?? Patient::factory(),
            'patient_name' => $patient?->name ?? 'Pasien Demo',
            'doctor_id' => $doctor?->user_id ?? Doctor::factory(),
            'doctor_name' => $doctor?->user->name ?? 'Dokter Demo',
            'schedule_id' => $schedule?->id ?? null,
            'date' => $this->faker->dateTimeBetween('2026-01-01', '2026-12-31'),
            'time_start' => $schedule?->time_start ?? '09:00:00',
            'time_end' => $schedule?->time_end ?? '10:00:00',
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'canceled']),
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/AppointmentFactory.php
git commit -m "feat: add Appointment factory"
```

---

## Task 6: Create Transaction Factory

**Files:**
- Create: `database/factories/TransactionFactory.php`

**Fields to generate:**
- All transaction fields with realistic dental clinic data

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first();
        $doctor = Doctor::inRandomOrder()->first();
        $appointment = Appointment::inRandomOrder()->first();
        $price = $this->faker->numberBetween(150000, 2500000);

        return [
            'id' => Str::uuid(),
            'sequence' => $this->faker->unique()->numberBetween(1, 9999),
            'has_down_payment' => $this->faker->boolean(30),
            'is_endorsed' => $this->faker->boolean(20),
            'has_installment' => $this->faker->boolean(15),
            'current_payment' => $this->faker->randomElement([0, $price * 0.5, $price]),
            'patient_id' => $patient?->id ?? Patient::factory(),
            'patient_name' => $patient?->name ?? 'Pasien Demo',
            'patient_phone' => $patient?->phone ?? '081234567890',
            'patient_code' => $patient?->code ?? 'PSG-0001',
            'doctor_id' => $doctor?->user_id ?? Doctor::factory(),
            'doctor_nipp' => $doctor?->nipp ?? 'NIPP001',
            'doctor_name' => $doctor?->user->name ?? 'Dokter Demo',
            'appointment_id' => $appointment?->id ?? null,
            'appointment_datetime' => $appointment?->date ?? now(),
            'next_schedule' => $this->faker->optional(0.6)->dateTimeBetween('+1 week', '+1 month'),
            'services' => json_encode([$this->faker->randomElement(['Pembersihan Karang Gigi', 'Tambal Gigi', 'Cabut Gigi', 'Scaling', 'Bleaching'])]),
            'price' => $price,
            'discount' => $this->faker->optional(0.3)->numberBetween(0, $price * 0.2),
            'billing' => $price,
            'payment_method' => $this->faker->randomElement(['cash', 'transfer', 'card', 'insurance']),
            'canceled_at' => null,
            'cancel_reason' => null,
            'voucher_code' => null,
            'is_locked' => false,
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/TransactionFactory.php
git commit -m "feat: add Transaction factory"
```

---

## Task 7: Create MedicalRecord Factory

**Files:**
- Create: `database/factories/MedicalRecordFactory.php`

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition(): array
    {
        $patient = Patient::inRandomOrder()->first();
        $doctor = Doctor::inRandomOrder()->first();

        return [
            'id' => Str::uuid(),
            'patient_id' => $patient?->id ?? Patient::factory(),
            'patient_name' => $patient?->name ?? 'Pasien Demo',
            'doctor_id' => $doctor?->user_id ?? Doctor::factory(),
            'doctor_name' => $doctor?->user->name ?? 'Dokter Demo',
            'date' => $this->faker->dateTimeBetween('2026-01-01', '2026-12-31'),
            'time_start' => '09:00:00',
            'time_end' => '10:00:00',
            'service' => $this->faker->randomElement(['Pembersihan Karang Gigi', 'Tambal Gigi', 'Cabut Gigi', 'Scaling', 'Bleaching', 'Root Canal', 'Crown']),
            'diagnose' => $this->faker->randomElement(['Gingivitis', 'Periodontitis', 'Karies Gigi', 'Fraktur Gigi', 'Maloklusi', 'Abses Gigi']),
            'therapy' => $this->faker->optional(0.7)->sentence,
            'prescription' => $this->faker->optional(0.5)->sentence,
            'next_schedule' => $this->faker->optional(0.6)->dateTimeBetween('+1 week', '+1 month'),
            'price' => $this->faker->numberBetween(150000, 2500000),
            'promat' => $this->faker->optional(0.3)->numerify('##/##'),
            'blood_tension' => $this->faker->optional(0.4)->numerify('##/##'),
            'cooperative' => $this->faker->randomElement(['Ya', 'Tidak', 'Kurang']),
            'image_before' => null,
            'image_after' => null,
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/MedicalRecordFactory.php
git commit -m "feat: add MedicalRecord factory"
```

---

## Task 8: Create PatientAddress Factory

**Files:**
- Create: `database/factories/PatientAddressFactory.php`

- [ ] **Step 1: Create the factory**

```php
<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientAddressFactory extends Factory
{
    protected $model = PatientAddress::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'zip_code' => $this->faker->numerify('#####'),
            'tonarigumi' => $this->faker->optional(0.3)->numerify('####'),
            'street' => $this->faker->streetAddress,
            'village' => $this->faker->citySuffix,
            'district' => $this->faker->city,
            'regency' => $this->faker->city,
            'province' => $this->faker->state,
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add database/factories/PatientAddressFactory.php
git commit -m "feat: add PatientAddress factory"
```

---

## Task 9: Update DatabaseSeeder with All Seed Data

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

**Strategy:** Seed 15 patients, 15 schedules, 15 appointments, 12 transactions, 12 medical records. Use `DB::beginTransaction()` inside a loop for performance.

- [ ] **Step 1: Update DatabaseSeeder**

Replace the existing DatabaseSeeder to include dummy data generation after the existing seeders.

- [ ] **Step 2: Commit**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "feat: add comprehensive dummy data seeder for all sections"
```

---

## Task 10: Run Seed and Verify

**Files:** None (verification only)

- [ ] **Step 1: Reset and reseed database**

```bash
php artisan migrate:fresh --seed
```

- [ ] **Step 2: Verify data counts**

```bash
php artisan tinker --execute="
echo 'Patients: ' . \App\Models\Patient::count() . PHP_EOL;
echo 'Schedules: ' . \App\Models\Schedule::count() . PHP_EOL;
echo 'Appointments: ' . \App\Models\Appointment::count() . PHP_EOL;
echo 'Transactions: ' . \App\Models\Transaction::count() . PHP_EOL;
echo 'Medical Records: ' . \App\Models\MedicalRecord::count() . PHP_EOL;
"
```

Expected: All counts >= 10

- [ ] **Step 3: Verify alert components work**

Start dev server and create a new record via form. Check that success message text is visible.

```bash
php artisan serve
```

---

## Summary of Deliverables

| Task | Deliverable |
|------|-------------|
| 1 | Fixed alert component syntax |
| 2 | Fixed Schedule model import |
| 3 | PatientFactory |
| 4 | ScheduleFactory |
| 5 | AppointmentFactory |
| 6 | TransactionFactory |
| 7 | MedicalRecordFactory |
| 8 | PatientAddressFactory |
| 9 | Updated DatabaseSeeder |
| 10 | Verified working seed data |
