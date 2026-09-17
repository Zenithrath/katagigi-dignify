<?php

namespace App\Http\Requests\General;

use App\Rules\AvailableDoctor;
use App\Rules\DoctorSchedule;
use App\Rules\TimeRangeUsed;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'doctor_id' => [
                'required',
                'not_in:""',
            ],
            'service_id' => [
                'required',
                'not_in:""',
            ],
            'date' => [
                'required',
                new AvailableDoctor($this->doctor_id, $this->date),
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
                new DoctorSchedule($this->doctor_id, $this->date, $this->start_time, $this->end_time),
                new TimeRangeUsed($this->date, $this->start_time, $this->end_time, $this->id, $this->doctor_id),
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                new DoctorSchedule($this->doctor_id, $this->date, $this->start_time, $this->end_time),
                new TimeRangeUsed($this->date, $this->start_time, $this->end_time, $this->id, $this->doctor_id),
            ],
        ];
    }
}
