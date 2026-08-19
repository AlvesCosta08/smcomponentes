<?php

namespace App\Actions\Admin\Produto;

use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AtualizarProdutoAction
{
    protected ProdutoRepository $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Executar atualização do produto
     */
    public function executar(Produto $produto, array $dados, $imagem = null): bool
    {
        // Gerar novo slug se descrição mudou
        if (isset($dados['descricao']) && $dados['descricao'] !== $produto->descricao) {
            $dados['slug'] = Str::slug($dados['descricao']);
            $dados['slug'] = $this->gerarSlugUnico($dados['slug'], $produto->id);
        }

        // Processar nova imagem
        if ($imagem) {
            // Deletar imagem antiga
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $dados['imagem'] = $this->processarImagem($imagem);
        }

        // Aplicar regras de disponibilidade
        $dados = $this->aplicarRegrasDisponibilidade($dados);

        // Atualizar produto
        $atualizado = $this->repository->atualizar($produto, $dados);

        // Log de atividade
        if ($atualizado) {
            Log::info('Produto atualizado', [
                'produto_id' => $produto->id,
                'descricao' => $produto->descricao,
                'usuario' => auth()->user()->email ?? 'sistema'
            ]);
        }

        return $atualizado;
    }

    /**
     * Gerar slug único (ignorando o produto atual)
     */
    private function gerarSlugUnico(string $slug, int $produtoId): string
    {
        $original = $slug;
        $contador = 1;

        while (Produto::where('slug', $slug)->where('id', '!=', $produtoId)->exists()) {
            $slug = $original . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    /**
     * Processar upload da imagem
     */
    private function processarImagem($imagem): string
    {
        $nome = Str::uuid() . '.' . $imagem->getClientOriginalExtension();
        $caminho = $imagem->storeAs('produtos', $nome, 'public');
        return $caminho;
    }

    /**
     * Aplicar regras de disponibilidade
     */
    private function aplicarRegrasDisponibilidade(array $dados): array
    {
        // Se quantidade for 0 ou negativa, marcar como indisponível
        if (isset($dados['quantidade']) && $dados['quantidade'] <= 0) {
            $dados['disponibilidade'] = 'INDISPONÍVEL';
        }

        return $dados;
    }
}