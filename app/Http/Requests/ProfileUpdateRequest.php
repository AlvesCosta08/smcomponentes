<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            
            // Contato
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            
            // 🔥 VALIDAÇÃO DE CPF
            'cpf' => [
                'nullable', 
                'string', 
                'max:14',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $cpf = preg_replace('/[^0-9]/', '', $value);
                        if (!User::validarCpf($cpf)) {
                            $fail('CPF inválido. Digite um CPF válido (ex: 111.222.333-44).');
                        }
                    }
                }
            ],
            
            // 🔥 VALIDAÇÃO DE CNPJ
            'cnpj' => [
                'nullable', 
                'string', 
                'max:18',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $cnpj = preg_replace('/[^0-9]/', '', $value);
                        if (!User::validarCnpj($cnpj)) {
                            $fail('CNPJ inválido. Digite um CNPJ válido (ex: 11.222.333/0001-44).');
                        }
                    }
                }
            ],
            
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            
            // Endereço
            'cep' => [
                'nullable', 
                'string', 
                'max:10',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $cep = preg_replace('/[^0-9]/', '', $value);
                        if (strlen($cep) != 8) {
                            $fail('CEP inválido. Digite um CEP com 8 dígitos (ex: 12345-678).');
                        }
                    }
                }
            ],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2', 'min:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'estado.min' => 'Digite a sigla do estado com 2 caracteres (ex: SP).',
            'estado.max' => 'Digite a sigla do estado com 2 caracteres (ex: SP).',
        ];
    }
}