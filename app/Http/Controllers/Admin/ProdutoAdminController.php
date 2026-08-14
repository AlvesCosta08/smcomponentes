<?php
// app/Http/Controllers/Admin/ProdutoAdminController.php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductDTO;
use App\DTOs\Responses\ProductResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProdutoRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdutoAdminController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Lista de produtos
     */
    public function index(Request $request): View
    {
        try {
            // Filtros
            $filters = [
                'categoria' => $request->get('categoria'),
                'ativo' => $request->get('ativo'),
                'estoque' => $request->get('estoque'),
                'destaque' => $request->get('destaque'),
                'promocao' => $request->get('promocao'),
                'search' => $request->get('search'),
                'ordenar_por' => $request->get('ordenar_por', 'created_at'),
                'ordenar_dir' => $request->get('ordenar_dir', 'desc'),
            ];

            // Remover filtros vazios
            $filters = array_filter($filters, function ($value) {
                return $value !== null && $value !== '';
            });

            // Buscar produtos usando o Service
            $produtos = $this->productService->listProducts($filters, 20);

            // Listar categorias para o filtro
            $categorias = $this->productService->getCategorias();

            // Estatísticas
            $estatisticas = $this->productService->getStats();

            return view('admin.produtos.index', compact(
                'produtos',
                'categorias',
                'estatisticas'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos: ' . $e->getMessage());

            $produtos = collect();
            $categorias = collect();
            $estatisticas = [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'com_estoque' => 0,
                'sem_estoque' => 0,
                'estoque_baixo' => 0,
                'em_destaque' => 0,
                'em_promocao' => 0,
                'disponiveis' => 0,
            ];

            return view('admin.produtos.index', compact(
                'produtos',
                'categorias',
                'estatisticas'
            ))->with('error', 'Erro ao carregar produtos: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de criação
     */
    public function create(): View
    {
        try {
            $categorias = $this->productService->getCategorias();
            return view('admin.produtos.create', compact('categorias'));
        } catch (\Exception $e) {
            Log::error('Erro ao abrir formulário de criação: ' . $e->getMessage());
            return redirect()
                ->route('admin.produtos.index')
                ->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Salvar novo produto
     */
    public function store(ProdutoRequest $request): RedirectResponse
    {
        try {
            // Criar DTO a partir do Request
            $dto = ProductDTO::fromRequest($request);

            // Criar produto via Service
            $produto = $this->productService->createProduct($dto);

            return redirect()
                ->route('admin.produtos.index')
                ->with('success', 'Produto criado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar produto
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $produto = $this->productService->getProductById($id);
            
            if (!$produto) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            return view('admin.produtos.show', compact('produto'));

        } catch (\Exception $e) {
            Log::error('Erro ao mostrar produto: ' . $e->getMessage());
            return redirect()
                ->route('admin.produtos.index')
                ->with('error', 'Erro ao carregar produto: ' . $e->getMessage());
        }
    }

    /**
     * Formulário de edição
     */
    public function edit(int $id): View|RedirectResponse
    {
        try {
            $produto = $this->productService->getProductById($id);
            
            if (!$produto) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            $categorias = $this->productService->getCategorias();

            return view('admin.produtos.edit', compact('produto', 'categorias'));

        } catch (\Exception $e) {
            Log::error('Erro ao abrir formulário de edição: ' . $e->getMessage());
            return redirect()
                ->route('admin.produtos.index')
                ->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar produto
     */
    public function update(ProdutoRequest $request, int $id): RedirectResponse
    {
        try {
            // Criar DTO a partir do Request para atualização
            $dto = ProductDTO::fromRequestUpdate($request, $id);

            // Atualizar produto via Service
            $produto = $this->productService->updateProduct($id, $dto);

            return redirect()
                ->route('admin.produtos.index')
                ->with('success', 'Produto atualizado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    /**
     * Excluir produto
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if (!$deleted) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            return redirect()
                ->route('admin.produtos.index')
                ->with('success', 'Produto excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
    }

    /**
     * Ajustar estoque
     */
    public function ajustarEstoque(Request $request, int $id): RedirectResponse
    {
        try {
            $request->validate([
                'quantidade' => 'required|integer|min:1',
                'tipo' => 'required|in:adicionar,remover'
            ]);

            $adjusted = $this->productService->adjustStock(
                $id,
                $request->quantidade,
                $request->tipo
            );

            if (!$adjusted) {
                return redirect()
                    ->back()
                    ->with('error', 'Erro ao ajustar estoque. Produto não encontrado.');
            }

            $mensagem = $request->tipo === 'adicionar' 
                ? 'adicionadas' 
                : 'removidas';

            return redirect()
                ->back()
                ->with('success', "{$request->quantidade} unidade(s) {$mensagem} do estoque com sucesso!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao ajustar estoque: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao ajustar estoque: ' . $e->getMessage());
        }
    }

    /**
     * Exportar produtos
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        try {
            $filters = [
                'categoria' => $request->get('categoria'),
                'ativo' => $request->get('ativo'),
                'estoque' => $request->get('estoque'),
            ];

            $filters = array_filter($filters, function ($value) {
                return $value !== null && $value !== '';
            });

            $path = $this->productService->export($filters);

            if (!file_exists($path)) {
                return redirect()
                    ->back()
                    ->with('warning', 'Nenhum produto encontrado para exportar.');
            }

            return response()->download($path)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar produtos: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao exportar produtos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar produtos via AJAX
     */
    public function searchAjax(Request $request): JsonResponse
    {
        try {
            $termo = $request->get('q');
            
            if (strlen($termo) < 2) {
                return response()->json([]);
            }

            $produtos = $this->productService->search($termo, 10);
            
            return response()->json([
                'success' => true,
                'data' => $produtos->items()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na busca AJAX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos'
            ], 500);
        }
    }

    /**
     * Atualizar status em massa
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:produtos,id',
                'action' => 'required|in:ativar,desativar,destaque,remover_destaque'
            ]);

            $action = $request->action;
            $ids = $request->ids;

            switch ($action) {
                case 'ativar':
                    $this->productService->bulkUpdate($ids, ['ativo' => true]);
                    $mensagem = 'Produtos ativados com sucesso!';
                    break;
                case 'desativar':
                    $this->productService->bulkUpdate($ids, ['ativo' => false]);
                    $mensagem = 'Produtos desativados com sucesso!';
                    break;
                case 'destaque':
                    $this->productService->bulkUpdate($ids, ['destaque' => true]);
                    $mensagem = 'Produtos marcados como destaque!';
                    break;
                case 'remover_destaque':
                    $this->productService->bulkUpdate($ids, ['destaque' => false]);
                    $mensagem = 'Produtos removidos do destaque!';
                    break;
                default:
                    return redirect()
                        ->back()
                        ->with('error', 'Ação inválida');
            }

            return redirect()
                ->back()
                ->with('success', $mensagem);

        } catch (\Exception $e) {
            Log::error('Erro ao executar ação em massa: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao executar ação: ' . $e->getMessage());
        }
    }
}