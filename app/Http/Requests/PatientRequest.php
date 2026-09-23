<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
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
            'picture' => 'max:1024',
            'name' => 'required|max:255',
            'email' => 'max:255',
            'payment_email' => 'max:255',
            'sosmed' => 'nullable',
            'phone' => 'max:255|required|regex:/^08[0-9]{8,13}$/',
            'religion' => 'nullable',
            // v3-MaritalStatus: S/M/W/D (dipetakan ke Patient.maritalStatus SSP).
            'marital_status' => ['nullable', Rule::in(['S', 'M', 'W', 'D'])],
            'gender' => 'nullable',
            'birthdate' => 'nullable|date',
            'birth_place' => 'nullable|string|max:255',
            'nik' => ['nullable', 'digits:16', Rule::unique('patients', 'nik')->ignore($this->route('patient'), 'id')],
            'ihs_id' => ['nullable', 'string', 'max:255', Rule::unique('patients', 'ihs_id')->ignore($this->route('patient'), 'id')],
            'satusehat_consent' => 'nullable|boolean',
            // Fase 4.3: penjamin saat pendaftaran.
            'insurance_id' => ['nullable', 'uuid', Rule::exists('master_insurances', 'id')],
            'insurance_number' => 'nullable|string|max:64',
            'zip_code' => 'max:255',
            'tonarigumi' => 'max:255',
            'street' => 'max:255',
            'village' => 'max:255',
            'district' => 'max:255',
            'regency' => 'max:255',
            'province' => 'max:255',
        ];
    }
}
