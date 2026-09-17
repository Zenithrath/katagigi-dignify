<?php

namespace App\Types\Entities;

class ScheduleEntity
{
    public ?string $id;

    public ?string $doctor_id;

    public ?string $day;

    public ?string $start_time;

    public ?string $end_time;

    public function fromRequest(array $validatedRequest, string $id)
    {
        $this->id = $id;
        $this->doctor_id = $validatedRequest['doctor_id'];
        $this->day = $validatedRequest['day'];
        $this->start_time = $validatedRequest['start_time'];
        $this->end_time = $validatedRequest['end_time'];
    }
}
