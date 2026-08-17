<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('usuario');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'password' => $isUpdate 
                ? ['nullable', 'confirmed', Password::min(8)]
                : ['required', 'confirmed', Password::min(8)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('users')->ignore($id),
            ],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('users')->ignore($id),
            ],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2', 'alpha'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'roles.*.exists' => 'Uma ou mais funções são inválidas.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->string('email'))),
            'cpf' => $this->has('cpf') ? preg_replace('/[^0-9]/', '', $this->string('cpf')) : null,
            'cnpj' => $this->has('cnpj') ? preg_replace('/[^0-9]/', '', $this->string('cnpj')) : null,
            'telefone' => $this->has('telefone') ? preg_replace('/[^0-9]/', '', $this->string('telefone')) : null,
            'celular' => $this->has('celular') ? preg_replace('/[^0-9]/', '', $this->string('celular')) : null,
            'cep' => $this->has('cep') ? preg_replace('/[^0-9]/', '', $this->string('cep')) : null,
            'ativo' => $this->boolean('ativo', true),
        ]);
    }
}