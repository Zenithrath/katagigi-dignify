<?php

namespace App\Rules;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class AvailableDoctor implements Rule
{
    protected $doctor_id;

    protected $date;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($doctor_id, $date)
    {
        $this->doctor_id = $doctor_id;
        $this->date = $date;
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
        $day = Carbon::parse($this->date)->format('l');

        return Schedule::where('doctor_id', $this->doctor_id)->where('day', strtoupper($day))->where('availability', 'AVAILABLE')->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The doctor isn't available on the selected date.";
    }
}
