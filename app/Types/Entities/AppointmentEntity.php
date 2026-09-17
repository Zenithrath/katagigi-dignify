<?php

namespace App\Types\Entities;

class AppointmentEntity
{
    public ?string $patient_code;

    public ?string $patient_id;

    public ?string $patient_name;

    public ?string $doctor_id;

    public ?string $doctor_nipp;

    public ?string $doctor_niptk;

    public ?string $doctor_name;

    public ?string $service_id;

    public ?string $date;

    public ?string $start_time;

    public ?string $end_time;

    public ?string $status;

    public function fromRequest(array $validatedRequest)
    {
        $this->patient_code = $validatedRequest['patient_code'];
        $this->patient_id = $validatedRequest['patient_id'];
        $this->patient_name = $validatedRequest['patient_name'];
        $this->doctor_id = $validatedRequest['doctor_id'];
        $this->doctor_nipp = $validatedRequest['doctor_nipp'];
        $this->doctor_niptk = $validatedRequest['doctor_niptk'];
        $this->doctor_name = $validatedRequest['doctor_name'];
        $this->service_id = $validatedRequest['service_id'];
        $this->date = $validatedRequest['date'];
        $this->start_time = $validatedRequest['start_time'];
        $this->end_time = $validatedRequest['end_time'];
        $this->status = $validatedRequest['status'];
    }

    public function updateRequest(array $validatedRequest)
    {
        $this->doctor_id = $validatedRequest['doctor_id'];
        $this->doctor_nipp = $validatedRequest['doctor_nipp'];
        $this->doctor_niptk = $validatedRequest['doctor_niptk'];
        $this->doctor_name = $validatedRequest['doctor_name'];
        $this->service_id = $validatedRequest['service_id'];
        $this->date = $validatedRequest['date'];
        $this->start_time = $validatedRequest['start_time'];
        $this->end_time = $validatedRequest['end_time'];
    }
}
