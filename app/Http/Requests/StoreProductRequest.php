<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'barcode' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2053',
            'unit_id' => 'required|integer',
            'category_id' => 'required|integer',
            'price' => 'required|decimal:0,2|min:0',
            'cost_price' => 'required|decimal:0,2|min:0',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean'
        ];
    }
}
