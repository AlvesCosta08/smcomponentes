<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PedidoApiController extends Controller
{
    /**
     * Listar todos os pedidos (Admin)
     */
    public function index()
    {
        return response()->json([
            "success" => true,
            "message" => "Lista de pedidos",
            "data" => []
        ]);
    }

    /**
     * Mostrar um pedido específico
     */
    public function show($pedido)
    {
        return response()->json([
            "success" => true,
            "message" => "Detalhes do pedido",
            "data" => [
                "id" => $pedido,
                "total" => 150.90,
                "status" => "pendente",
                "created_at" => now()->toIso8601String()
            ]
        ]);
    }

    /**
     * Listar pedidos do usuário logado
     */
    public function meusPedidos(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Meus pedidos",
            "data" => []
        ]);
    }

    /**
     * Criar um novo pedido
     */
    public function store(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Pedido criado com sucesso!",
            "data" => $request->all()
        ], 201);
    }

    /**
     * Atualizar status do pedido (Admin)
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            "success" => true,
            "message" => "Pedido $id atualizado com sucesso!",
            "data" => $request->all()
        ]);
    }

    /**
     * Cancelar pedido
     */
    public function cancel($id)
    {
        return response()->json([
            "success" => true,
            "message" => "Pedido $id cancelado com sucesso!"
        ]);
    }
}
