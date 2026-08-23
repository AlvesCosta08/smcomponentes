<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Assumindo que o middleware de auth já validou o usuário
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:produtos,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'shipping_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.min' => 'O pedido deve conter pelo menos um item.',
            'items.*.product_id.exists' => 'Um dos produtos informados não existe.',
        ];
    }
}