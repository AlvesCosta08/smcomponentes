<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostra a página de edição do perfil
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza os dados do perfil
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Campos permitidos para atualização
        $allowedFields = [
            'name', 'email', 
            'telefone', 'celular', 
            'cpf', 'cnpj', 'data_nascimento',  // 🔥 ADICIONADO CNPJ
            'cep', 'logradouro', 'numero', 'complemento', 
            'bairro', 'cidade', 'estado'
        ];

        foreach ($allowedFields as $field) {
            if (isset($validated[$field])) {
                $user->$field = $validated[$field];
            }
        }

        // 🔥 VALIDA CPF SE FOR FORNECIDO
        if (isset($validated['cpf']) && !empty($validated['cpf'])) {
            $cpfLimpo = preg_replace('/[^0-9]/', '', $validated['cpf']);
            if (!User::validarCpf($cpfLimpo)) {
                return back()->withErrors(['cpf' => 'CPF inválido'])->withInput();
            }
            $user->cpf = $cpfLimpo;
        }

        // 🔥 VALIDA CNPJ SE FOR FORNECIDO
        if (isset($validated['cnpj']) && !empty($validated['cnpj'])) {
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $validated['cnpj']);
            if (!User::validarCnpj($cnpjLimpo)) {
                return back()->withErrors(['cnpj' => 'CNPJ inválido'])->withInput();
            }
            $user->cnpj = $cnpjLimpo;
        }

        // 🔥 VALIDA CELULAR SE FOR FORNECIDO
        if (isset($validated['celular']) && !empty($validated['celular'])) {
            $celularLimpo = preg_replace('/[^0-9]/', '', $validated['celular']);
            if (strlen($celularLimpo) < 10 || strlen($celularLimpo) > 11) {
                return back()->withErrors(['celular' => 'Celular inválido. Use o formato (11) 99999-9999'])->withInput();
            }
            $user->celular = $celularLimpo;
        }

        // 🔥 VALIDA TELEFONE SE FOR FORNECIDO
        if (isset($validated['telefone']) && !empty($validated['telefone'])) {
            $telefoneLimpo = preg_replace('/[^0-9]/', '', $validated['telefone']);
            if (strlen($telefoneLimpo) < 8 || strlen($telefoneLimpo) > 10) {
                return back()->withErrors(['telefone' => 'Telefone inválido. Use o formato (11) 9999-9999'])->withInput();
            }
            $user->telefone = $telefoneLimpo;
        }

        // 🔥 VALIDA CEP SE FOR FORNECIDO
        if (isset($validated['cep']) && !empty($validated['cep'])) {
            $cepLimpo = preg_replace('/[^0-9]/', '', $validated['cep']);
            if (strlen($cepLimpo) != 8) {
                return back()->withErrors(['cep' => 'CEP inválido. Use o formato 00000-000'])->withInput();
            }
            $user->cep = $cepLimpo;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Atualiza a senha do usuário
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Exclui a conta do usuário
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // 🔥 DESATIVA EM VEZ DE DELETAR (SOFT DELETE)
        $user->ativo = false;
        $user->save();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Sua conta foi desativada com sucesso.');
    }
}
