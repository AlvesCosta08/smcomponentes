<?php

namespace App\Actions\Admin\Produto;

use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CriarProdutoAction
{
    protected ProdutoRepository $repository;

    public function __construct(ProdutoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Executar criação do produto
     */
    public function executar(array $dados, $imagem = null): Produto
    {
        // Gerar slug
        $dados['slug'] = Str::slug($dados['descricao']);

        // Garantir que o slug é único
        $dados['slug'] = $this->gerarSlugUnico($dados['slug']);

        // Processar imagem se existir
        if ($imagem) {
            $dados['imagem'] = $this->processarImagem($imagem);
        }

        // Aplicar regras de disponibilidade
        $dados = $this->aplicarRegrasDisponibilidade($dados);

        // Ativar por padrão
        $dados['ativo'] = $dados['ativo'] ?? true;

        // Criar produto
        $produto = $this->repository->criar($dados);

        // Log de atividade
        Log::info('Produto criado', [
            'produto_id' => $produto->id,
            'descricao' => $produto->descricao,
            'usuario' => auth()->user()->email ?? 'sistema'
        ]);

        return $produto;
    }

    /**
     * Gerar slug único
     */
    private function gerarSlugUnico(string $slug): string
    {
        $original = $slug;
        $contador = 1;

        while (Produto::where('slug', $slug)->exists()) {
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
        if (($dados['quantidade'] ?? 0) <= 0) {
            $dados['disponibilidade'] = 'INDISPONÍVEL';
        }

        return $dados;
    }
}