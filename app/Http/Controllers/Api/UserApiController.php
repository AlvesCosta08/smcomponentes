<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Listar todos os usuários (Admin)
     */
    public function index()
    {
        return response()->json([
            "success" => true,
            "message" => "Lista de usuários",
            "data" => []
        ]);
    }

    /**
     * Mostrar um usuário específico
     */
    public function show($id)
    {
        return response()->json([
            "success" => true,
            "message" => "Detalhes do usuário",
            "data" => [
                "id" => $id,
                "name" => "Usuário Exemplo",
                "email" => "usuario@exemplo.com",
                "created_at" => now()->toIso8601String()
            ]
        ]);
    }

    /**
     * Criar um novo usuário
     */
    public function store(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Usuário criado com sucesso!",
            "data" => $request->all()
        ], 201);
    }

    /**
     * Atualizar um usuário
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            "success" => true,
            "message" => "Usuário $id atualizado com sucesso!",
            "data" => $request->all()
        ]);
    }

    /**
     * Deletar um usuário
     */
    public function destroy($id)
    {
        return response()->json([
            "success" => true,
            "message" => "Usuário $id deletado com sucesso!"
        ]);
    }

    /**
     * Atualizar perfil do usuário logado
     */
    public function updateProfile(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Perfil atualizado com sucesso!",
            "data" => $request->all()
        ]);
    }
}
