<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user) {
            Log::warning('Tentativa de verificação sem usuário autenticado', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
            ]);
            return false;
        }

        // Verificar se o ID e hash correspondem
        $hash = sha1($user->getEmailForVerification());
        
        if (!hash_equals($hash, (string) $this->route('hash'))) {
            Log::warning('Hash de verificação inválido', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $this->ip(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'hash' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID do usuário é obrigatório.',
            'id.integer' => 'ID do usuário deve ser um número inteiro.',
            'id.exists' => 'Usuário não encontrado.',
            'hash.required' => 'Hash de verificação é obrigatório.',
        ];
    }
}