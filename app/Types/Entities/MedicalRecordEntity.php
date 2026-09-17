<?php

namespace App\Types\Entities;

class MedicalRecordEntity
{
    public ?string $patient_id;

    public ?string $patient_name;

    public ?string $patient_address;

    public ?string $doctor_id;

    public ?string $doctor_name;

    public ?string $appointment_id;

    public ?string $date;

    public ?string $time_start;

    public ?string $time_end;

    public ?string $service;

    public ?string $diagnose;

    public ?string $therapy;

    public ?string $prescription;

    public ?string $next_schedule;

    public ?float $price;

    public ?string $promat;

    public ?string $blood_tension;

    public ?string $cooperative;

    public ?string $image_before;

    public ?string $image_after;

    public function fromRequest(array $validatedRequest, string $image_before_path, string $image_after_path)
    {
        $this->patient_id = $validatedRequest['patient_id'];
        $this->patient_name = $validatedRequest['patient_name'];
        $this->patient_address = $validatedRequest['patient_address'];
        $this->doctor_id = $validatedRequest['doctor_id'];
        $this->doctor_name = $validatedRequest['doctor_name'];
        $this->appointment_id = $validatedRequest['appointment_id'];
        $this->date = $validatedRequest['date'];
        $this->time_start = $validatedRequest['time_start'];
        $this->time_end = $validatedRequest['time_end'];
        $this->service = $validatedRequest['service'];
        $this->diagnose = $validatedRequest['diagnose'];
        $this->therapy = $validatedRequest['therapy'];
        $this->prescription = $validatedRequest['prescription'];
        $this->next_schedule = $validatedRequest['next_schedule'];
        $this->price = $validatedRequest['price'];
        $this->promat = $validatedRequest['promat'];
        $this->blood_tension = $validatedRequest['blood_tension'];
        $this->cooperative = $validatedRequest['cooperative'];
        $this->image_before = $image_before_path;
        $this->image_after = $image_after_path;
    }
}
