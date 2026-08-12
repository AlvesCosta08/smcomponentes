<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // 🔥 NOVO: Permite atualizar outros campos
        $allowedFields = [
            'name', 'email', 'telefone', 'cpf', 
            'data_nascimento', 'cep', 'logradouro', 
            'numero', 'complemento', 'bairro', 
            'cidade', 'estado'
        ];

        foreach ($allowedFields as $field) {
            if (isset($validated[$field])) {
                $user->$field = $validated[$field];
            }
        }

        // 🔥 MELHORIA: Valida CPF se for fornecido
        if (isset($validated['cpf']) && !empty($validated['cpf'])) {
            if (!User::validarCpf($validated['cpf'])) {
                return back()->withErrors(['cpf' => 'CPF inválido']);
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // 🔥 NOVO: Método para atualizar senha separadamente
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // 🔥 NOVO: Desativa em vez de deletar (soft delete)
        $user->ativo = false;
        $user->save();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}