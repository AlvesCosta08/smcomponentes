<?php
// app/Http/Requests/Admin/UpdatePedidoStatusRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pendente,pago,processando,enviado,entregue,cancelado'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
        ];
    }
}