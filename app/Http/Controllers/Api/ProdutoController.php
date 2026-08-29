<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Produtos",
 *     description="Operações relacionadas a produtos"
 * )
 */
class ProdutoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/produtos",
     *     summary="Listar todos os produtos",
     *     tags={"Produtos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de produtos retornada com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Produto")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Produto::all();
    }

    /**
     * @OA\Post(
     *     path="/api/produtos",
     *     summary="Criar um novo produto",
     *     tags={"Produtos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome","preco"},
     *             @OA\Property(property="nome", type="string", example="Produto Exemplo"),
     *             @OA\Property(property="preco", type="number", format="float", example=99.90),
     *             @OA\Property(property="descricao", type="string", example="Descrição do produto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produto criado com sucesso"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $produto = Produto::create($request->all());
        return response()->json($produto, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/produtos/{id}",
     *     summary="Buscar um produto específico",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produto não encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        return Produto::findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/api/produtos/{id}",
     *     summary="Atualizar um produto",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nome", type="string", example="Produto Atualizado"),
     *             @OA\Property(property="preco", type="number", format="float", example=149.90),
     *             @OA\Property(property="descricao", type="string", example="Nova descrição")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->update($request->all());
        return $produto;
    }

    /**
     * @OA\Delete(
     *     path="/api/produtos/{id}",
     *     summary="Excluir um produto",
     *     tags={"Produtos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Produto excluído com sucesso"
     *     )
     * )
     */
    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        $produto->delete();
        return response()->noContent();
    }
}
