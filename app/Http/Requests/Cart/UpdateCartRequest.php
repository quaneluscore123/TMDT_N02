<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.min' => 'Số lượng phải ít nhất là 1.',
            'quantity.max' => 'Số lượng không được vượt quá 100.',
        ];
    }
}
