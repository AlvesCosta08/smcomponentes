<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['nullable', 'string', 'max:18', Rule::unique(User::class)->ignore($userId)],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:2'],
        ];
    }

    // 🔥 NOVO: Mensagens customizadas
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'Informe um email válido',
            'email.unique' => 'Este email já está em uso',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'data_nascimento.before' => 'Data de nascimento inválida',
        ];
    }
}