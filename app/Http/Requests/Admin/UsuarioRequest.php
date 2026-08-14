<?php
// app/Http/Requests/Admin/UsuarioRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function rules(): array
    {
        $userId = $this->route('usuario') ? $this->route('usuario')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users')->ignore($userId)],
            'data_nascimento' => 'nullable|date',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'role' => 'required|exists:roles,name',
            'ativo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não coincide.',
            'role.required' => 'Selecione uma função para o usuário.',
            'role.exists' => 'Função inválida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Remover máscaras
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/[^0-9]/', '', $this->cpf)
            ]);
        }

        if ($this->has('telefone')) {
            $this->merge([
                'telefone' => preg_replace('/[^0-9]/', '', $this->telefone)
            ]);
        }

        if ($this->has('cep')) {
            $this->merge([
                'cep' => preg_replace('/[^0-9]/', '', $this->cep)
            ]);
        }

        // Garantir que role seja uma string válida
        if ($this->has('role')) {
            $this->merge([
                'role' => trim($this->role)
            ]);
        }
    }
}