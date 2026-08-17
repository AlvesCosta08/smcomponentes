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
        $validated = $request->validated();

        // Remover campos de senha (já são tratados separadamente)
        unset($validated['current_password'], $validated['password'], $validated['password_confirmation']);

        // Limpar documentos
        $validated = $this->limparDocumentos($validated);

        // Validar documentos
        $this->validarDocumentos($validated);

        // Atualizar apenas campos preenchidos
        foreach ($validated as $field => $value) {
            if ($value !== null && $value !== '') {
                $user->$field = $value;
            }
        }

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

            return Redirect::route('profile.edit')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        return Redirect::route('profile.edit')
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

        return back()->with('success', 'Senha atualizada com sucesso!');
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

        // Log antes de desativar
        Log::info('👤 Conta desativada', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        Auth::logout();

        // Desativar em vez de deletar (soft delete)
        $user->ativo = false;
        $user->save();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')
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
            return redirect()->route('dashboard')
                ->with('info', 'Sua conta já está ativa.');
        }

        $user->ativo = true;
        $user->save();

        Log::info('🔄 Conta reativada', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Sua conta foi reativada com sucesso!');
    }

    // ================================================================
    // MÉTODOS PRIVADOS
    // ================================================================

    /**
     * Limpa caracteres especiais de documentos.
     */
    private function limparDocumentos(array $data): array
    {
        $campos = ['cpf', 'cnpj', 'telefone', 'celular', 'cep'];

        foreach ($campos as $campo) {
            if (isset($data[$campo]) && !empty($data[$campo])) {
                $data[$campo] = preg_replace('/[^0-9]/', '', $data[$campo]);
            }
        }

        return $data;
    }

    /**
     * Valida documentos do usuário.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validarDocumentos(array $data): void
    {
        $errors = [];

        // Validar CPF
        if (!empty($data['cpf'])) {
            if (!User::validarCpf($data['cpf'])) {
                $errors['cpf'] = 'CPF inválido.';
            }
        }

        // Validar CNPJ
        if (!empty($data['cnpj'])) {
            if (!User::validarCnpj($data['cnpj'])) {
                $errors['cnpj'] = 'CNPJ inválido.';
            }
        }

        // Validar CELULAR
        if (!empty($data['celular'])) {
            $length = strlen($data['celular']);
            if ($length < 10 || $length > 11) {
                $errors['celular'] = 'Celular inválido. Use o formato (11) 99999-9999';
            }
        }

        // Validar TELEFONE
        if (!empty($data['telefone'])) {
            $length = strlen($data['telefone']);
            if ($length < 8 || $length > 10) {
                $errors['telefone'] = 'Telefone inválido. Use o formato (11) 9999-9999';
            }
        }

        // Validar CEP
        if (!empty($data['cep'])) {
            if (strlen($data['cep']) !== 8) {
                $errors['cep'] = 'CEP inválido. Use o formato 00000-000';
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /**
     * Obtém o histórico de alterações do perfil.
     */
    public function historico(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // Buscar logs do usuário (se tiver tabela de logs)
        // $logs = ActivityLog::where('causer_id', $user->id)
        //     ->where('log_name', 'user')
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(20);

        // Por enquanto, retorna view vazia
        return view('profile.historico', [
            'user' => $user,
            // 'logs' => $logs ?? collect(),
        ]);
    }
}