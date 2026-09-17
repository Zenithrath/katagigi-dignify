<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TimeRangeUsed implements Rule
{
    protected $id;

    protected $doctor_id;

    protected $date;

    protected $start_time;

    protected $end_time;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($date, $start_time, $end_time, $id, $doctor_id)
    {
        $this->id = $id;
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
        $start = Carbon::parse($this->start_time)->format('H:i:s');
        $finish = Carbon::parse($this->end_time)->format('H:i:s');

        return ! DB::table('appointments')
            ->where('date', '=', $this->date)
            ->where(function ($query) use ($start, $finish) {
                $query->where(function ($q) use ($start, $finish) {
                    $q->where('time_start', '>=', $start)
                        ->where('time_start', '<', $finish);
                })->orWhere(function ($q) use ($start, $finish) {
                    $q->where('time_end', '>', $start)
                        ->where('time_end', '<=', $finish);
                });
            })
            ->where('doctor_id', $this->doctor_id)
            ->whereNot('id', $this->id)
            ->whereNull('canceled_at')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('confirmed_at')
                        ->whereNull('paid_at')
                        ->whereNull('canceled_at')
                        ->whereNull('recorded_at');
                })->orWhere(function ($q) {
                    $q->whereNotNull('confirmed_at')
                        ->whereNull('paid_at')
                        ->whereNull('canceled_at')
                        ->whereNull('recorded_at');
                })->orWhere(function ($q) {
                    $q->whereNull('confirmed_at')
                        ->whereNotNull('paid_at')
                        ->whereNull('canceled_at')
                        ->whereNull('recorded_at');
                })->orWhere(function ($q) {
                    $q->whereNull('confirmed_at')
                        ->whereNull('paid_at')
                        ->whereNotNull('canceled_at')
                        ->whereNull('recorded_at');
                })->orWhere(function ($q) {
                    $q->whereNull('confirmed_at')
                        ->whereNull('paid_at')
                        ->whereNull('canceled_at')
                        ->whereNotNull('recorded_at');
                });
            })
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Appointment time is already taken.';
    }
}
