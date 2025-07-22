<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'po_number' => 'required|string|unique:purchaseOrders',
            'supplier_id' => 'required|integer',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date',
            'status' => 'required|string',
            'total_amount' => 'required|decimal:2,0|min:0',
            'notes' => 'nullable|string',
            'user_id' => 'required|string'
        ];
    }
}
