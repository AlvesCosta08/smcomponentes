<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UsuarioAdminController extends Controller
{
    /**
     * Listar todos os usuários
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Busca por nome, email ou CPF
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('cpf', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por role
        if ($request->has('role') && !empty($request->role)) {
            $query->role($request->role);
        }

        // Filtro por status
        if ($request->has('status') && $request->status !== '') {
            $query->where('ativo', $request->status);
        }

        // Filtro por data de cadastro
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $usuarios = $query->with('roles')->orderBy('created_at', 'desc')->paginate(15);
        
        // Roles para o filtro
        $roles = Role::all()->pluck('name');
        
        // Estatísticas
        $totalUsuarios = User::count();
        $totalClientes = User::role('Cliente')->count();
        $totalAdmins = User::role('Admin')->count();
        $totalFuncionarios = User::role('Funcionario')->count();
        $usuariosAtivos = User::where('ativo', true)->count();
        $usuariosInativos = User::where('ativo', false)->count();

        return view('admin.usuarios.index', compact(
            'usuarios',
            'roles',
            'totalUsuarios',
            'totalClientes',
            'totalAdmins',
            'totalFuncionarios',
            'usuariosAtivos',
            'usuariosInativos'
        ));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Salvar novo usuário
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:users,cpf',
            'data_nascimento' => 'nullable|date',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'role' => 'required|exists:roles,name',
            'ativo' => 'boolean'
        ]);

        // Criar usuário
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telefone' => $validated['telefone'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'cpf' => $validated['cpf'] ?? null,
            'data_nascimento' => $validated['data_nascimento'] ?? null,
            'cep' => $validated['cep'] ?? null,
            'logradouro' => $validated['logradouro'] ?? null,
            'numero' => $validated['numero'] ?? null,
            'complemento' => $validated['complemento'] ?? null,
            'bairro' => $validated['bairro'] ?? null,
            'cidade' => $validated['cidade'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'ativo' => $validated['ativo'] ?? true,
        ]);

        // Atribuir role
        $user->assignRole($validated['role']);

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', "Usuário '{$user->name}' criado com sucesso!");
    }

    /**
     * Mostrar detalhes do usuário
     */
    public function show(User $usuario)
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
    public function edit(User $usuario)
    {
        $roles = Role::all();
        $userRole = $usuario->roles->first()->name ?? null;
        
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'userRole'));
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users')->ignore($usuario->id)],
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
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        // Dados para atualizar
        $dados = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telefone' => $validated['telefone'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'cpf' => $validated['cpf'] ?? null,
            'data_nascimento' => $validated['data_nascimento'] ?? null,
            'cep' => $validated['cep'] ?? null,
            'logradouro' => $validated['logradouro'] ?? null,
            'numero' => $validated['numero'] ?? null,
            'complemento' => $validated['complemento'] ?? null,
            'bairro' => $validated['bairro'] ?? null,
            'cidade' => $validated['cidade'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'ativo' => $validated['ativo'] ?? true,
        ];

        // Atualizar senha se fornecida
        if (!empty($validated['password'])) {
            $dados['password'] = Hash::make($validated['password']);
        }

        $usuario->update($dados);

        // Atualizar role
        $usuario->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', "Usuário '{$usuario->name}' atualizado com sucesso!");
    }

    /**
     * Deletar usuário (soft delete)
     */
    public function destroy(User $usuario)
    {
        // Não permitir deletar o próprio usuário
        if ($usuario->id === auth()->id()) {
            return redirect()
                ->back()
                ->with('error', 'Você não pode deletar seu próprio usuário.');
        }

        // Verificar se tem pedidos
        if ($usuario->pedidos()->whereIn('status', ['pendente', 'pago', 'processando'])->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Usuário possui pedidos pendentes. Não pode ser deletado.');
        }

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', "Usuário '{$usuario->name}' deletado com sucesso!");
    }

    /**
     * Ativar/Desativar usuário
     */
    public function toggleStatus(User $usuario)
    {
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
    }

    /**
     * Restaurar usuário (soft delete)
     */
    public function restore($id)
    {
        $usuario = User::onlyTrashed()->findOrFail($id);
        $usuario->restore();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', "Usuário '{$usuario->name}' restaurado com sucesso!");
    }

    /**
     * Histórico de pedidos do usuário
     */
    public function historicoPedidos(User $usuario)
    {
        $pedidos = $usuario->pedidos()->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.usuarios.historico', compact('usuario', 'pedidos'));
    }
}