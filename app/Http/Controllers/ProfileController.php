<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostra o perfil do usuário.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Mostra a página de edição do perfil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza os dados do perfil.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        // ✅ OBTÉM OS DADOS VALIDADOS COMO ARRAY
        $validated = $request->validated();

        // Remover campos de senha (já são tratados separadamente)
        unset($validated['current_password'], $validated['password'], $validated['password_confirmation']);

        // ✅ ATUALIZAR DIRETAMENTE OS CAMPOS
        $user->fill($validated);

        // Se o email foi alterado, marcar como não verificado
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;

            Log::info('📧 E-mail alterado', [
                'user_id' => $user->id,
                'email_antigo' => $user->getOriginal('email'),
                'email_novo' => $user->email
            ]);
        }

        // Verificar se houve mudanças
        if ($user->isDirty()) {
            $user->save();

            Log::info('👤 Perfil atualizado', [
                'user_id' => $user->id,
                'campos' => array_keys($user->getDirty())
            ]);

            return redirect()->route('cliente.perfil.edit')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        return redirect()->route('cliente.perfil.edit')
            ->with('info', 'Nenhuma alteração foi feita.');
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        Log::info('🔑 Senha alterada', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('cliente.perfil.edit')
            ->with('success', 'Senha atualizada com sucesso!');
    }

    /**
     * Exclui/desativa a conta do usuário.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();

        Log::info('👤 Conta desativada', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        Auth::logout();

        $user->ativo = false;
        $user->save();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Sua conta foi desativada com sucesso.');
    }

    /**
     * Reativa uma conta desativada.
     */
    public function reativar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para reativar sua conta.');
        }

        if ($user->ativo) {
            return redirect()->route('cliente.dashboard')
                ->with('info', 'Sua conta já está ativa.');
        }

        $user->ativo = true;
        $user->save();

        Log::info('🔄 Conta reativada', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('cliente.dashboard')
            ->with('success', 'Sua conta foi reativada com sucesso!');
    }

    /**
     * Obtém o histórico de alterações do perfil.
     */
    public function historico(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('profile.historico', [
            'user' => $user,
        ]);
    }
}