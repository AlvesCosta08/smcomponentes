<?php
// app/Http/Controllers/Admin/UsuarioAdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class UsuarioAdminController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Listar todos os usuários
     */
    public function index(Request $request): View
    {
        try {
            $filters = $request->only(['search', 'role', 'status', 'data_inicio', 'data_fim']);
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });

            $usuarios = $this->userService->listUsers($filters, 15);
            
            // Estatísticas
            $stats = $this->userService->getStats();
            $roles = $this->userService->getRoles();

            return view('admin.usuarios.index', [
                'usuarios' => $usuarios,
                'roles' => $roles,
                'totalUsuarios' => $stats['total'],
                'totalClientes' => $stats['clientes'],
                'totalAdmins' => $stats['admins'],
                'totalFuncionarios' => $stats['funcionarios'],
                'usuariosAtivos' => $stats['ativos'],
                'usuariosInativos' => $stats['inativos'],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar usuários: ' . $e->getMessage());
            
            return view('admin.usuarios.index', [
                'usuarios' => collect(),
                'roles' => [],
                'totalUsuarios' => 0,
                'totalClientes' => 0,
                'totalAdmins' => 0,
                'totalFuncionarios' => 0,
                'usuariosAtivos' => 0,
                'usuariosInativos' => 0,
            ])->with('error', 'Erro ao carregar usuários: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulário de criação
     */
    public function create(): View
    {
        $roles = $this->userService->getRoles();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Salvar novo usuário
     */
    public function store(UsuarioRequest $request): RedirectResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', "Usuário '{$user->name}' criado com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalhes do usuário
     */
    public function show(User $usuario): View
    {
        $usuario->load(['roles', 'pedidos' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);
        
        $totalPedidos = $usuario->pedidos->count();
        $totalGasto = $usuario->pedidos->sum('total');
        $ultimoPedido = $usuario->pedidos()->latest()->first();

        return view('admin.usuarios.show', compact(
            'usuario',
            'totalPedidos',
            'totalGasto',
            'ultimoPedido'
        ));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(User $usuario): View
    {
        $roles = $this->userService->getRoles();
        $userRole = $usuario->roles->first()->name ?? null;
        
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'userRole'));
    }

    /**
     * Atualizar usuário
     */
    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        try {
            $this->userService->updateUser($usuario, $request->validated());

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', "Usuário '{$usuario->name}' atualizado com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Deletar usuário (soft delete)
     */
    public function destroy(User $usuario): RedirectResponse
    {
        try {
            // Não permitir deletar o próprio usuário
            if ($usuario->id === auth()->id()) {
                return redirect()
                    ->back()
                    ->with('error', 'Você não pode deletar seu próprio usuário.');
            }

            // Verificar se tem pedidos pendentes
            if ($usuario->pedidos()->whereIn('status', ['pendente', 'pago', 'processando'])->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Usuário possui pedidos pendentes. Não pode ser deletado.');
            }

            $usuario->delete();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', "Usuário '{$usuario->name}' deletado com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao deletar usuário: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao deletar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Ativar/Desativar usuário
     */
    public function toggleStatus(User $usuario): RedirectResponse
    {
        try {
            // Não permitir desativar o próprio usuário
            if ($usuario->id === auth()->id()) {
                return redirect()
                    ->back()
                    ->with('error', 'Você não pode desativar seu próprio usuário.');
            }

            $usuario->ativo = !$usuario->ativo;
            $usuario->save();

            $status = $usuario->ativo ? 'ativado' : 'desativado';
            return redirect()
                ->back()
                ->with('success', "Usuário '{$usuario->name}' foi {$status}!");

        } catch (\Exception $e) {
            Log::error('Erro ao alternar status: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao alternar status: ' . $e->getMessage());
        }
    }

    /**
     * Restaurar usuário (soft delete)
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $usuario = User::onlyTrashed()->findOrFail($id);
            $usuario->restore();

            return redirect()
                ->route('admin.usuarios.index')
                ->with('success', "Usuário '{$usuario->name}' restaurado com sucesso!");

        } catch (\Exception $e) {
            Log::error('Erro ao restaurar usuário: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao restaurar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Histórico de pedidos do usuário
     */
    public function historicoPedidos(User $usuario): View
    {
        $pedidos = $usuario->pedidos()->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.usuarios.historico', compact('usuario', 'pedidos'));
    }
}