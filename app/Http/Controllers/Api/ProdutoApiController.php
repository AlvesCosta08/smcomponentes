<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProdutoApiController extends Controller
{
    /**
     * Listar todos os produtos
     */
    public function index()
    {
        return response()->json([
            "success" => true,
            "message" => "Lista de produtos",
            "data" => []
        ]);
    }

    /**
     * Listar produtos em destaque
     */
    public function destaques()
    {
        return response()->json([
            "success" => true,
            "message" => "Produtos em destaque",
            "data" => []
        ]);
    }

    /**
     * Listar produtos em oferta
     */
    public function ofertas()
    {
        return response()->json([
            "success" => true,
            "message" => "Produtos em oferta",
            "data" => []
        ]);
    }

    /**
     * Listar produtos novos
     */
    public function novos()
    {
        return response()->json([
            "success" => true,
            "message" => "Produtos novos",
            "data" => []
        ]);
    }

    /**
     * Listar produtos mais vendidos
     */
    public function maisVendidos()
    {
        return response()->json([
            "success" => true,
            "message" => "Produtos mais vendidos",
            "data" => []
        ]);
    }

    /**
     * Mostrar um produto específico
     */
    public function show($slug)
    {
        return response()->json([
            "success" => true,
            "message" => "Detalhes do produto",
            "data" => [
                "id" => 1,
                "slug" => $slug,
                "nome" => "Produto Exemplo",
                "preco" => 99.90,
                "descricao" => "Descrição do produto"
            ]
        ]);
    }

    /**
     * Criar um novo produto
     */
    public function store(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Produto criado com sucesso!",
            "data" => $request->all()
        ], 201);
    }

    /**
     * Atualizar um produto
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            "success" => true,
            "message" => "Produto $id atualizado com sucesso!",
            "data" => $request->all()
        ]);
    }

    /**
     * Deletar um produto
     */
    public function destroy($id)
    {
        return response()->json([
            "success" => true,
            "message" => "Produto $id deletado com sucesso!"
        ]);
    }

    /**
     * Listar produtos para administração
     */
    public function adminIndex()
    {
        return response()->json([
            "success" => true,
            "message" => "Lista de produtos - Admin",
            "data" => []
        ]);
    }

    /**
     * Buscar produtos por termo
     */
    public function search(Request $request)
    {
        $term = $request->get("q", "");
        return response()->json([
            "success" => true,
            "message" => "Resultados para: $term",
            "data" => []
        ]);
    }

    /**
     * Filtrar produtos
     */
    public function filter(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Produtos filtrados",
            "data" => []
        ]);
    }

    /**
     * Atualizar estoque
     */
    public function updateStock(Request $request, $id)
    {
        return response()->json([
            "success" => true,
            "message" => "Estoque do produto $id atualizado",
            "data" => $request->all()
        ]);
    }
}
