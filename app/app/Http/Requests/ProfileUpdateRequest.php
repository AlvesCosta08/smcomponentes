<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            // Dados pessoais
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user?->id),
            ],
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('users')->ignore($user?->id),
            ],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('users')->ignore($user?->id),
            ],
            'data_nascimento' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'ie' => ['nullable', 'string', 'max:20'],

            // Endereço
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2', 'alpha'],

            // Senha (opcional)
            'current_password' => ['nullable', 'string', 'required_with:password'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8),
                'different:current_password',
            ],
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
            'name.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'A data de nascimento deve ser posterior a 1900.',
            'estado.alpha' => 'O estado deve conter apenas letras.',
            'estado.max' => 'O estado deve ter 2 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não corresponde.',
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
            'current_password.required_with' => 'A senha atual é obrigatória para alterar a senha.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->string('email'))),
            'cpf' => $this->limparDocumento($this->input('cpf')),
            'cnpj' => $this->limparDocumento($this->input('cnpj')),
            'telefone' => $this->limparDocumento($this->input('telefone')),
            'celular' => $this->limparDocumento($this->input('celular')),
            'cep' => $this->limparDocumento($this->input('cep')),
        ]);
    }

    /**
     * Limpa caracteres especiais de um documento.
     */
    private function limparDocumento(?string $valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $valor);
    }
}