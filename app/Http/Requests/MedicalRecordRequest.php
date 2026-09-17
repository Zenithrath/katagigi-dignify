<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalRecordRequest extends FormRequest
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

    private function removeNullTrailingInput(string $fieldName)
    {
        $field = $this->input("$fieldName");
        if (! $field) {
            return;
        }
        unset($field[count($field) - 1]);
        $this->replace([
            ...$this->input(),
            "$fieldName" => $field,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'appointment_id' => 'required',
            'checkup_result' => 'required',
            'anamnesis' => 'required',
            'diagnosis' => 'required',
            'therapy' => 'required',
            'prescription' => '',
            'promat' => 'required',
            'blood_pressure' => 'required',
            'cooperativity' => 'required',
            'next_schedule' => '',
            'price' => 'required',
            'discount' => 'required',
            'billing' => 'required',
            'image_before' => 'required|array',
            'image_before.*' => 'max:1024',
            'image_after' => 'required|array',
            'image_after.*' => 'max:1024',
            'service_price' => 'required|array',
            'service_price.*' => 'required|numeric',
            'service_quantity' => 'required|array',
            'service_quantity.*' => 'required|numeric',
            'service_id' => 'required|array',
            'service_id.*' => 'required|string',
            'service_discount' => 'required|array',
            'service_discount.*' => '',
        ];

        $this->removeNullTrailingInput('image_before_meta');
        if (! $this->has('image_before')) {
            unset($rules['image_before']);
            unset($rules['image_before.*']);
            $rules['image_before_meta'] = 'required|array';
            $rules['image_before_meta.*'] = 'string';
        }

        $this->removeNullTrailingInput('image_after_meta');
        if (! $this->has('image_after')) {
            unset($rules['image_after']);
            unset($rules['image_after.*']);
            $rules['image_after_meta'] = 'required|array';
            $rules['image_after_meta.*'] = 'string';
        }

        return $rules;
    }
}
