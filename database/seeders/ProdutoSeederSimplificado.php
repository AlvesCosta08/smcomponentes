<?php

namespace Database\Seeders;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdutoSeederSimplificado extends Seeder
{
    /**
     * Arquivo CSV na raiz do projeto
     * ATENÇÃO: Use o arquivo ORIGINAL (Produtos.csv), NÃO o convertido!
     */
    private const CSV_FILE = 'Produtos.csv';

    public function run(): void
    {
        $csvPath = base_path(self::CSV_FILE);

        if (!file_exists($csvPath)) {
            $this->command->error("❌ CSV não encontrado: {$csvPath}");
            return;
        }

        // Abre o arquivo em modo binário (evita problemas de encoding)
        $handle = fopen($csvPath, 'rb');
        if (!$handle) {
            $this->command->error("❌ Não foi possível abrir o CSV");
            return;
        }

        // Lê cabeçalho
        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            $this->command->error("❌ CSV vazio ou inválido");
            fclose($handle);
            return;
        }

        // Remove BOM se existir
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        // Normaliza nomes das colunas (remove espaços)
        $header = array_map('trim', $header);

        $this->command->info("📦 Importando produtos...");
        $this->command->newLine();

        $importados = 0;
        $atualizados = 0;
        $ignorados = 0;
        $erros = 0;
        $semPreco = 0;

        $bar = $this->command->getOutput()->createProgressBar();

        DB::beginTransaction();

        try {
            $linha = 0;

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $linha++;
                $bar->advance();

                // Ignora linhas com número diferente de colunas
                if (count($row) !== count($header)) {
                    $ignorados++;
                    continue;
                }

                $data = array_combine($header, $row);

                // =====================================================
                // ✅ NORMALIZAÇÃO AGRESSIVA DE UTF-8
                // =====================================================
                foreach ($data as $key => $value) {
                    if (!is_string($value)) {
                        continue;
                    }

                    $value = trim($value);

                    // 1. Remove BOM se estiver no meio da string
                    $value = str_replace("\xEF\xBB\xBF", '', $value);

                    // 2. Remove bytes de controle (exceto \n \r \t)
                    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

                    // 3. Se NÃO é UTF-8 válido, aí sim tenta converter
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                    }

                    // 4. Última defesa: normaliza como UTF-8 puro
                    //    (remove qualquer byte inválido remanescente)
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

                    // 5. Remove caracteres de substituição Unicode (�)
                    $value = str_replace("\xEF\xBF\xBD", '', $value);

                    $data[$key] = $value;
                }

                // Pula linhas sem descrição
                if (empty($data['descricao'])) {
                    $ignorados++;
                    continue;
                }

                // =====================================================
                // ✅ CORREÇÃO: Categoria com slug único (evita erro 1062)
                // =====================================================
                $categoriaId = null;
                if (!empty($data['categoria']) && $data['categoria'] !== 'NULL') {
                    $categoriaNome = trim($data['categoria']);
                    $slug = Str::slug($categoriaNome);

                    // 1. Busca por NOME exato
                    $categoria = Categoria::where('nome', $categoriaNome)->first();

                    // 2. Se não achou, busca por SLUG (evita duplicatas com nomes parecidos)
                    if (!$categoria) {
                        $categoria = Categoria::where('slug', $slug)->first();
                    }

                    // 3. Se ainda não existe, cria com slug ÚNICO
                    if (!$categoria) {
                        $slugFinal = $slug;
                        $contador = 1;
                        while (Categoria::where('slug', $slugFinal)->exists()) {
                            $slugFinal = $slug . '-' . $contador;
                            $contador++;
                        }

                        $categoria = Categoria::create([
                            'nome' => $categoriaNome,
                            'slug' => $slugFinal,
                            'ativo' => true,
                        ]);
                    }

                    $categoriaId = $categoria->id;
                }

                // Helper para decimais (vírgula → ponto)
                $parseDecimal = function ($v) {
                    if ($v === '' || $v === null || $v === 'NULL' || $v === 'null') {
                        return null;
                    }
                    return (float) str_replace(',', '.', trim($v));
                };

                // Status baseado na disponibilidade
                $disponibilidade = strtoupper($data['disponibilidade'] ?? '');
                $status = match ($disponibilidade) {
                    'DISPONÍVEL'     => 'disponivel',
                    'INDISPONIVEL'   => 'indisponivel',
                    'EST.BAIXO'      => 'disponivel',
                    'SOB ENCOMENDA'  => 'sob_encomenda',
                    default          => 'indisponivel',
                };

                // Se não tem valor_unitario mas tem valor_atacado, usa o atacado
                $valorUnitario = $parseDecimal($data['valor_unitario'] ?? null);
                $valorAtacado  = $parseDecimal($data['valor_atacado'] ?? null);

                if ($valorUnitario === null && $valorAtacado !== null) {
                    $valorUnitario = $valorAtacado;
                }
                if ($valorAtacado === null && $valorUnitario !== null) {
                    $valorAtacado = $valorUnitario;
                }

                // Se ambos null, usa 0.01 simbólico
                if ($valorUnitario === null && $valorAtacado === null) {
                    $valorUnitario = 0.01;
                    $valorAtacado  = 0.01;
                    $semPreco++;
                }

                // =====================================================
                // ✅ Garante slug ÚNICO
                // =====================================================
                $slugBase = !empty($data['slug']) && $data['slug'] !== 'NULL'
                    ? $data['slug']
                    : Str::slug($data['descricao']);

                $referencia = $this->nullify($data['referencia'] ?? null);
                $slug = $slugBase;
                $contador = 1;
                while (Produto::where('slug', $slug)
                    ->where(function ($q) use ($referencia) {
                        if ($referencia === null) {
                            $q->whereNotNull('referencia');
                        } else {
                            $q->where('referencia', '!=', $referencia)
                              ->orWhereNull('referencia');
                        }
                    })
                    ->exists()
                ) {
                    $slug = $slugBase . '-' . $contador;
                    $contador++;
                }

                // Monta dados
                $dados = [
                    'categoria_id'          => $categoriaId,
                    'referencia'            => $referencia,
                    'descricao'             => $data['descricao'],
                    'tipo'                  => $data['tipo'] ?? 'UNI',
                    'slug'                  => $slug,
                    'quantidade'            => (int) ($data['quantidade'] ?? 0),
                    'estoque_minimo'        => (int) ($data['estoque_minimo'] ?? 5),
                    'valor_unitario'        => $valorUnitario,
                    'valor_atacado'         => $valorAtacado,
                    'preco_promocional'     => $parseDecimal($data['preco_promocional'] ?? null),
                    'ativo'                 => (int) ($data['ativo'] ?? 1),
                    'destaque'              => (int) ($data['destaque'] ?? 0),
                    'novo'                  => (int) ($data['novo'] ?? 0),
                    'mais_vendido'          => (int) ($data['mais_vendido'] ?? 0),
                    'visualizacoes'         => (int) ($data['visualizacoes'] ?? 0),
                    'fornecedor'            => $this->nullify($data['fornecedor'] ?? null),
                    'imagem'                => $this->nullify($data['imagem'] ?? null),
                    'status'                => $status,
                ];

                // Remove campos null específicos
                $dados = array_filter($dados, function ($value, $key) {
                    return !in_array($key, ['referencia', 'fornecedor', 'imagem'])
                        || $value !== null;
                }, ARRAY_FILTER_USE_BOTH);

                try {
                    $produto = Produto::updateOrCreate(
                        ['slug' => $slug],
                        $dados
                    );

                    if ($produto->wasRecentlyCreated) {
                        $importados++;
                    } else {
                        $atualizados++;
                    }
                } catch (\Exception $e) {
                    $erros++;
                    $this->command->newLine();
                    $this->command->error("Linha {$linha}: " . $e->getMessage());
                }
            }

            DB::commit();
            $bar->finish();
            $this->command->newLine(2);

            $this->command->info("✅ Importação concluída!");
            $this->command->table(
                ['Métrica', 'Quantidade'],
                [
                    ['Importados (novos)', $importados],
                    ['Atualizados', $atualizados],
                    ['Sem preço (R$ 0,01)', $semPreco],
                    ['Ignorados', $ignorados],
                    ['Erros', $erros],
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->command->newLine(2);
            $this->command->error("❌ Erro fatal: " . $e->getMessage());
        }

        fclose($handle);
    }

    /**
     * Retorna null se for vazio ou string "NULL"
     */
    private function nullify($value): ?string
    {
        if ($value === null) return null;
        $value = trim($value);
        if ($value === '' || strtoupper($value) === 'NULL') return null;
        return $value;
    }
}