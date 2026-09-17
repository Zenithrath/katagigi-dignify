<?php

namespace App\Rules;

use App\Models\Schedule;
use Carbon\Carbon;
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
        $this->day = $day;
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
        $day = strtoupper(Carbon::parse($this->day)->format('l'));

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
        return 'The day working doctor is already exist.';
    }
}
