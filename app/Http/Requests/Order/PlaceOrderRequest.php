<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.grocery_item_id' => ['required', 'integer', 'exists:grocery_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required to place an order.',
            'items.*.grocery_item_id.exists' => 'One or more selected grocery items do not exist.',
            'items.*.quantity.min' => 'Each item quantity must be at least 1.',
        ];
    }
}
