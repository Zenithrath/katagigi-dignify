<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', Rule::unique('users')->ignore($this->user)],
            'password' => ['required', 'confirmed', Password::min(8)],
            'nipp' => ['required', Rule::unique('admins')->ignore($this->admin)],
            'niptk' => 'nullable',
            // 'niptk' => [Rule::unique('admins')->ignore($this->admin)],
            // 'zip_code' => ['regex:/^[0-9\-]+$/u'],
            // 'tonarigumi' => ['regex:/^[a-zA-Z0-9,.\- ]+$/u'],
            // 'street' => ['regex:/^[a-zA-Z0-9,.\- ]+$/u'],
            // 'village' => ['regex:/^[a-zA-Z,.\- ]+$/u'],
            // 'district' => ['regex:/^[a-zA-Z,.\- ]+$/u'],
            // 'regency' => ['regex:/^[a-zA-Z,.\- ]+$/u'],
            // 'province' => ['regex:/^[a-zA-Z,.\- ]+$/u'],
            'zip_code' => ['max:255'],
            'tonarigumi' => ['max:255'],
            'street' => ['max:255'],
            'village' => ['max:255'],
            'district' => ['max:255'],
            'regency' => ['max:255'],
            'province' => ['max:255'],
            'cover_image' => ['max:1024'],
            'profile_image' => ['max:1024'],
        ];
    }
}
