<?php

namespace App\Http\Requests\General;

use App\Rules\AvailableDoctor;
use App\Rules\DoctorSchedule;
use App\Rules\TimeRangeUsed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
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
     * Normalisasi no HP pasien baru sebelum validasi: buang spasi/
     * strip/plus, 62… atau 8… dijadikan 08… .
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('new_patient_phone')) {
            $digits = preg_replace('/\D+/', '', (string) $this->input('new_patient_phone'));
            if (str_starts_with($digits, '62')) {
                $digits = '0'.substr($digits, 2);
            } elseif (str_starts_with($digits, '8')) {
                $digits = '0'.$digits;
            }
            $this->merge(['new_patient_phone' => $digits]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // Pasien terdaftar (dipilih dari hasil cari) ATAU pasien baru
            // (quick-add di form yang sama) — salah satu wajib diisi.
            'patient_id' => ['required_without:new_patient_name'],
            'new_patient_name' => ['nullable', 'string', 'max:255', 'required_without:patient_id'],
            'new_patient_phone' => ['nullable', 'regex:/^08[0-9]{8,13}$/', 'required_with:new_patient_name'],
            'new_patient_birthdate' => ['nullable', 'date', 'required_with:new_patient_name'],
            'new_patient_gender' => ['nullable', Rule::in(['MALE', 'FEMALE']), 'required_with:new_patient_name'],
            'doctor_id' => [
                'required',
                'not_in:""',
            ],
            'service_id' => [
                'required',
                'array',
                'min:1',
            ],
            'service_id.*' => [
                'required',
                'string',
                'distinct',
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
