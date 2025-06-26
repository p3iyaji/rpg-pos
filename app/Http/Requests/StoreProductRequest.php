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
            'barcode' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2053',
            'unit_id' => 'required',
            'category_id' => 'required',
            'price' => 'required|integer',
            'cost_price' => 'required|integer',
            'quantity' => 'required|integer',
            'is_active' => 'required|boolean'
        ];
    }
}
