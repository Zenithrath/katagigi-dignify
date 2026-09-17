<?php

namespace App\Rules;

use App\Models\Schedule;
use Illuminate\Contracts\Validation\Rule;

class DoctorDay implements Rule
{
    protected $doctor_id;

    protected $day;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($doctor_id, $day)
    {
        $this->doctor_id = $doctor_id;
        $this->day = strtoupper($day);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = Schedule::where('doctor_id', $this->doctor_id)
            ->where('day', $this->day)
            ->exists();

        return ! $data;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Jadwal dokter untuk hari ini sudah ada.';
    }
}
