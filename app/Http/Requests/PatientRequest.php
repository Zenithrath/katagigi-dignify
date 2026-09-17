<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'gender' => 'nullable',
            'birthdate' => 'nullable',
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
