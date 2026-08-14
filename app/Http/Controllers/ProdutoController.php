<?php
// app/Http/Controllers/ProdutoController.php

namespace App\Http\Controllers;

use App\DTOs\ProductDTO;
use App\DTOs\Responses\ProductResponseDTO;
use App\Http\Requests\Produto\FiltroProdutoRequest;
use App\Http\Requests\Produto\BuscarProdutoRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProdutoController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Listar produtos com filtros
     */
    public function index(FiltroProdutoRequest $request): View
    {
        // Buscar produtos com os filtros validados
        $produtos = $this->productService->listProducts(
            filters: $request->validated(),
            perPage: 24
        );

        // Estatísticas
        $totais = $this->productService->getStats();

        return view('produtos.index', compact('produtos', 'totais'));
    }

    /**
     * Mostrar detalhes do produto
     */
    public function show(string $slug): View
    {
        // Buscar produto por slug
        $produto = $this->productService->findBySlug($slug);
        
        if (!$produto) {
            abort(404, 'Produto não encontrado');
        }

        // Incrementar visualizações
        $this->productService->incrementarVisualizacoes($produto->id);

        // Buscar produtos relacionados
        $relacionados = $this->productService->getRelacionados(
            produtoId: $produto->id,
            categoria: $produto->categoria,
            limit: 6
        );

        return view('produtos.show', compact('produto', 'relacionados'));
    }

    /**
     * Filtrar produtos por categoria
     */
    public function porCategoria(string $categoria, FiltroProdutoRequest $request): View
    {
        $produtos = $this->productService->listProducts(
            filters: array_merge($request->validated(), ['categoria' => $categoria]),
            perPage: 24
        );

        $totais = $this->productService->getStats();

        return view('produtos.index', compact('produtos', 'totais'));
    }

    /**
     * Buscar produtos
     */
    public function buscar(BuscarProdutoRequest $request): View
    {
        $search = $request->input('q');
        $produtos = $this->productService->search($search, 24);
        $totais = $this->productService->getStats();

        return view('produtos.index', compact('produtos', 'search', 'totais'));
    }

    /**
     * Filtro por disponibilidade
     */
    public function filtroDisponibilidade(string $status, FiltroProdutoRequest $request): View
    {
        $produtos = $this->productService->listProducts(
            filters: array_merge($request->validated(), ['disponibilidade' => $status]),
            perPage: 24
        );

        $totais = $this->productService->getStats();

        return view('produtos.index', compact('produtos', 'totais'));
    }

    /**
     * API: Produtos em destaque
     */
    public function destaques(): JsonResponse
    {
        $produtos = $this->productService->getDestaques(8);
        
        return response()->json($produtos);
    }
}