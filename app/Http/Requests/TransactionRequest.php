<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'appointment_id' => 'required',
            'voucher_code' => 'nullable|string',
            'service_id' => 'required|array',
            'service_id.*' => 'required|string|distinct',
            'service_price' => 'required|array',
            'service_price.*' => 'required|numeric',
            'service_quantity' => 'required|array',
            'service_quantity.*' => 'required|numeric',
            'service_discount' => 'array',
            'service_discount.*' => '',
            'is_installment' => 'nullable|string',
            'down_payment_amount' => 'nullable|numeric',
            'installment_period' => 'nullable|numeric',
            'installment_step' => 'nullable|string',
            'next_schedule' => '',
            'discount' => '',
            'price' => 'required',
            'billing' => 'required',
            'assistant_id' => '',
            'payment_method' => 'required',
        ];
    }
}
