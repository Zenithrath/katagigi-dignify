<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'confirmed',
            'street' => 'required|string|max:255',
            'village' => 'required|string|max:255',
            'tonarigumi' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'regency' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'nipp' => 'required|string|max:255',
            'niptk' => 'nullable|string|max:255',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
