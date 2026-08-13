<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdutoAdminController extends Controller
{
    protected $produtoRepository;

    public function __construct(ProdutoRepository $produtoRepository)
    {
        $this->produtoRepository = $produtoRepository;
    }

    /**
     * Lista de produtos
     */
    public function index(Request $request)
    {
        try {
            // Filtros
            $filtros = [
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
            $filtros = array_filter($filtros, function($value) {
                return $value !== null && $value !== '';
            });

            // Buscar produtos
            $produtos = $this->produtoRepository->listarProdutos($filtros, 20);

            // Listar categorias para o filtro
            $categorias = $this->produtoRepository->listarCategorias();

            // Estatísticas - Garantir que todas as chaves existam
            $estatisticas = $this->produtoRepository->obterEstatisticas();
            
            // Garantir que a chave 'disponiveis' exista para compatibilidade
            if (!isset($estatisticas['disponiveis'])) {
                $estatisticas['disponiveis'] = $estatisticas['com_estoque'] ?? 0;
            }

            return view('admin.produtos.index', compact(
                'produtos',
                'categorias',
                'estatisticas'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao listar produtos: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Dados vazios para fallback
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
    public function create()
    {
        try {
            $categorias = $this->produtoRepository->listarCategorias();
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
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao' => 'required|string|max:255',
                'categoria' => 'required|string|max:100',
                'referencia' => 'nullable|string|max:50',
                'valor_unitario' => 'required|numeric|min:0',
                'preco_promocional' => 'nullable|numeric|min:0',
                'quantidade' => 'required|integer|min:0',
                'estoque_minimo' => 'nullable|integer|min:0',
                'ativo' => 'boolean',
                'destaque' => 'boolean',
                'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Upload da imagem
            $imagem = null;
            if ($request->hasFile('imagem')) {
                $imagem = $request->file('imagem')->store('produtos', 'public');
            }

            // Gerar slug
            $slug = Str::slug($request->descricao . '-' . ($request->referencia ?? 'produto'));
            $slugOriginal = $slug;
            $contador = 1;
            while (Produto::where('slug', $slug)->exists()) {
                $slug = $slugOriginal . '-' . $contador;
                $contador++;
            }

            // Criar produto
            $produto = $this->produtoRepository->criar([
                'descricao' => $request->descricao,
                'categoria' => $request->categoria,
                'referencia' => $request->referencia,
                'valor_unitario' => $request->valor_unitario,
                'preco_promocional' => $request->preco_promocional,
                'quantidade' => $request->quantidade,
                'estoque_minimo' => $request->estoque_minimo ?? 5,
                'ativo' => $request->has('ativo') ? 1 : 0,
                'destaque' => $request->has('destaque') ? 1 : 0,
                'imagem' => $imagem,
                'slug' => $slug,
                'disponibilidade' => $request->quantidade > 0 ? 'DISPONIVEL' : 'INDISPONIVEL',
            ]);

            return redirect()
                ->route('admin.produtos.index')
                ->with('success', 'Produto criado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar produto: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar produto
     */
    public function show($id)
    {
        try {
            $produto = $this->produtoRepository->buscarPorId($id);
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
    public function edit($id)
    {
        try {
            $produto = $this->produtoRepository->buscarPorId($id);
            if (!$produto) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            $categorias = $this->produtoRepository->listarCategorias();

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
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'descricao' => 'required|string|max:255',
                'categoria' => 'required|string|max:100',
                'referencia' => 'nullable|string|max:50',
                'valor_unitario' => 'required|numeric|min:0',
                'preco_promocional' => 'nullable|numeric|min:0',
                'quantidade' => 'required|integer|min:0',
                'estoque_minimo' => 'nullable|integer|min:0',
                'ativo' => 'boolean',
                'destaque' => 'boolean',
                'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $produto = $this->produtoRepository->buscarPorId($id);
            if (!$produto) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            $dados = [
                'descricao' => $request->descricao,
                'categoria' => $request->categoria,
                'referencia' => $request->referencia,
                'valor_unitario' => $request->valor_unitario,
                'preco_promocional' => $request->preco_promocional,
                'quantidade' => $request->quantidade,
                'estoque_minimo' => $request->estoque_minimo ?? 5,
                'ativo' => $request->has('ativo') ? 1 : 0,
                'destaque' => $request->has('destaque') ? 1 : 0,
                'disponibilidade' => $request->quantidade > 0 ? 'DISPONIVEL' : 'INDISPONIVEL',
            ];

            // Upload da imagem
            if ($request->hasFile('imagem')) {
                // Remover imagem antiga
                if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
                    Storage::disk('public')->delete($produto->imagem);
                }
                $dados['imagem'] = $request->file('imagem')->store('produtos', 'public');
            }

            // Gerar slug se descrição mudou
            if ($request->descricao !== $produto->descricao) {
                $slug = Str::slug($request->descricao . '-' . ($request->referencia ?? 'produto'));
                $slugOriginal = $slug;
                $contador = 1;
                while (Produto::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $slugOriginal . '-' . $contador;
                    $contador++;
                }
                $dados['slug'] = $slug;
            }

            $this->produtoRepository->atualizar($id, $dados);

            return redirect()
                ->route('admin.produtos.index')
                ->with('success', 'Produto atualizado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
    }

    /**
     * Excluir produto
     */
    public function destroy($id)
    {
        try {
            $produto = $this->produtoRepository->buscarPorId($id);
            if (!$produto) {
                return redirect()
                    ->route('admin.produtos.index')
                    ->with('error', 'Produto não encontrado');
            }

            // Remover imagem
            if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
                Storage::disk('public')->delete($produto->imagem);
            }

            $this->produtoRepository->excluir($id);

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
    public function ajustarEstoque(Request $request, $id)
    {
        try {
            $request->validate([
                'quantidade' => 'required|integer|min:1',
                'tipo' => 'required|in:adicionar,remover'
            ]);

            $produto = $this->produtoRepository->ajustarEstoque(
                $id,
                $request->quantidade,
                $request->tipo
            );

            if (!$produto) {
                return redirect()
                    ->back()
                    ->with('error', 'Produto não encontrado');
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
    public function export(Request $request)
    {
        try {
            $filtros = [
                'categoria' => $request->get('categoria'),
                'ativo' => $request->get('ativo'),
                'estoque' => $request->get('estoque'),
            ];

            $filtros = array_filter($filtros, function($value) {
                return $value !== null && $value !== '';
            });

            $produtos = $this->produtoRepository->listarProdutos($filtros, 9999);

            if ($produtos->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('warning', 'Nenhum produto encontrado para exportar.');
            }

            $filename = 'produtos_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($produtos) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");

                // Cabeçalhos
                fputcsv($handle, [
                    'ID',
                    'Referência',
                    'Descrição',
                    'Categoria',
                    'Preço Unitário',
                    'Preço Promocional',
                    'Quantidade',
                    'Estoque Mínimo',
                    'Disponibilidade',
                    'Status',
                    'Destaque',
                    'Data Criação'
                ]);

                foreach ($produtos as $produto) {
                    fputcsv($handle, [
                        $produto->id,
                        $produto->referencia ?? 'N/A',
                        $produto->descricao,
                        $produto->categoria,
                        number_format($produto->valor_unitario, 2, ',', '.'),
                        $produto->preco_promocional ? number_format($produto->preco_promocional, 2, ',', '.') : 'N/A',
                        $produto->quantidade,
                        $produto->estoque_minimo ?? 0,
                        $produto->disponibilidade ?? 'N/A',
                        $produto->ativo ? 'Ativo' : 'Inativo',
                        $produto->destaque ? 'Sim' : 'Não',
                        $produto->created_at ? $produto->created_at->format('d/m/Y H:i') : 'N/A'
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar produtos: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao exportar produtos: ' . $e->getMessage());
        }
    }
}