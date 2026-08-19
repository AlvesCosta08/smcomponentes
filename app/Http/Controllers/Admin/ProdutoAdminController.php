<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductDTO;
use App\DTOs\Requests\CreateProductRequestDTO;
use App\DTOs\Responses\ProductResponseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProdutoRequest;
use App\Models\Categoria;
use App\Models\Produto;
use App\Models\ProdutoImagem;
use App\Services\ProdutoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProdutoAdminController extends Controller
{
    public function __construct(
        private readonly ProdutoService $produtoService
    ) {}

    public function index(Request $request): View
    {
        $query = Produto::query()->with(['categoria']);
        
        if ($request->has('busca') && $request->busca) {
            $query->buscar($request->busca);
        }
        
        if ($request->has('status') && $request->status) {
            $query->when($request->status === 'disponivel', fn($q) => $q->disponivel())
                 ->when($request->status === 'indisponivel', fn($q) => $q->where('disponibilidade', 'INDISPONIVEL'))
                 ->when($request->status === 'estoque_baixo', fn($q) => $q->baixoEstoque())
                 ->when($request->status === 'inativo', fn($q) => $q->where('ativo', false));
        }
        
        if ($request->has('categoria') && $request->categoria) {
            $query->where('categoria_id', $request->categoria);
        }
        
        $produtos = $query->latest()->paginate(15);
        $categorias = Categoria::ativo()->ordenado()->get();

        $estatisticas = [
            'total' => Produto::count(),
            'com_estoque' => Produto::disponivel()->count(),
            'estoque_baixo' => Produto::baixoEstoque()->count(),
            'sem_estoque' => Produto::where('quantidade', 0)->count(),
            'ativos' => Produto::where('ativo', true)->count(),
            'inativos' => Produto::where('ativo', false)->count(),
        ];
        
        return view('admin.produtos.index', compact('produtos', 'categorias', 'estatisticas'));
    }

    public function create(): View
    {
        $categorias = Categoria::ativo()->ordenado()->get();
        $margens = Produto::getMargensDisponiveis();
        return view('admin.produtos.create', compact('categorias', 'margens'));
    }

    public function store(ProdutoRequest $request): RedirectResponse
    {
        $dto = CreateProductRequestDTO::fromRequest($request);
        
        $errors = $dto->validate();
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $produto = $this->produtoService->create($dto);
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function show(int $id): View
    {
        $produto = Produto::with(['categoria', 'imagens'])->findOrFail($id);
        return view('admin.produtos.show', compact('produto'));
    }

    public function edit(int $id): View
    {
        $produto = Produto::with(['imagens'])->findOrFail($id);
        $categorias = Categoria::ativo()->ordenado()->get();
        $margens = Produto::getMargensDisponiveis();
        return view('admin.produtos.edit', compact('produto', 'categorias', 'margens'));
    }

    public function update(ProdutoRequest $request, int $id): RedirectResponse
    {
        $produto = Produto::findOrFail($id);
        $dto = CreateProductRequestDTO::fromRequest($request);
        
        $errors = $dto->validate();
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $this->produtoService->update($produto, $dto);
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $produto = Produto::findOrFail($id);
        $this->produtoService->delete($produto);
        
        return redirect()
            ->route('admin.produtos.index')
            ->with('success', 'Produto excluído com sucesso!');
    }

    public function ajustarEstoque(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'operacao' => 'required|in:adicionar,remover,definir',
        ]);
        
        $produto = Produto::findOrFail($id);
        
        $mensagem = match($request->operacao) {
            'adicionar' => $this->produtoService->adicionarEstoque($produto, $request->quantidade),
            'remover' => $this->produtoService->removerEstoque($produto, $request->quantidade),
            'definir' => $this->produtoService->definirEstoque($produto, $request->quantidade),
        };
        
        return back()->with('success', $mensagem);
    }

    public function removerImagem(int $id): JsonResponse
    {
        $imagem = ProdutoImagem::findOrFail($id);
        Storage::disk('public')->delete($imagem->imagem);
        $imagem->delete();
        return response()->json(['success' => true]);
    }

    public function definirPrincipal(int $id): JsonResponse
    {
        $imagem = ProdutoImagem::findOrFail($id);
        ProdutoImagem::where('produto_id', $imagem->produto_id)->update(['principal' => false]);
        $imagem->principal = true;
        $imagem->save();
        return response()->json(['success' => true]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $produtos = Produto::with('categoria')->get();
        
        $filename = 'produtos_' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($handle, [
            'ID', 'Descrição', 'Categoria', 'Referência', 'Tipo',
            'Preço Atacado', 'Valor Unitário', 'Valor Compra', 'Valor Custo',
            'IPI (%)', 'Preço com IPI', 'Margem Lucro (%)', 'Percentual Custo',
            'Quantidade', 'Estoque Mínimo', 'Disponibilidade',
            'Ativo', 'Destaque', 'Novo', 'Mais Vendido',
            'Data Compra', 'Data Criação'
        ], ';');
        
        foreach ($produtos as $produto) {
            fputcsv($handle, [
                $produto->id,
                $produto->descricao,
                $produto->categoria,
                $produto->referencia ?? '',
                $produto->tipo ?? '',
                number_format($produto->valor_atacado ?? 0, 2, ',', '.'),
                number_format($produto->valor_unitario ?? 0, 2, ',', '.'),
                number_format($produto->valor_compra ?? 0, 2, ',', '.'),
                number_format($produto->valor_custo ?? 0, 2, ',', '.'),
                number_format($produto->ipi ?? 0, 2, ',', '.'),
                number_format($produto->preco_com_ipi ?? 0, 2, ',', '.'),
                number_format($produto->margem_lucro ?? 0, 2, ',', '.'),
                number_format($produto->percentual_custo ?? 0, 2, ',', '.'),
                $produto->quantidade,
                $produto->estoque_minimo ?? 5,
                $produto->disponibilidade?->label() ?? '',
                $produto->ativo ? 'Sim' : 'Não',
                $produto->destaque ? 'Sim' : 'Não',
                $produto->novo ? 'Sim' : 'Não',
                $produto->mais_vendido ? 'Sim' : 'Não',
                $produto->data_compra?->format('d/m/Y') ?? '',
                $produto->created_at?->format('d/m/Y H:i') ?? '',
            ], ';');
        }
        
        fclose($handle);
        
        return response()->stream(
            function() {},
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}