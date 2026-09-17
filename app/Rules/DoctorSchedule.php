<?php

namespace App\Rules;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class DoctorSchedule implements Rule
{
    protected $doctor_id;

    protected $date;

    protected $start_time;

    protected $end_time;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($doctor_id, $date, $start_time, $end_time)
    {
        $this->doctor_id = $doctor_id;
        $this->date = $date;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
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
        $day = strtoupper(Carbon::parse($this->date)->format('l'));

        return Schedule::where('doctor_id', $this->doctor_id)
            ->where('day', $day)
            ->where('availability', 'AVAILABLE')
            ->where('time_start', '<=', $this->start_time)
            ->where('time_end', '>=', $this->end_time)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The doctor is not in the clinic at the selected time.';
    }
}
