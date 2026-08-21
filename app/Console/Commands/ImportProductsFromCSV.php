<?php

namespace App\Console\Commands;

use App\Models\Produto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportProductsFromCSV extends Command
{
    protected $signature = 'import:products-from-csv
                            {--file=Produtos.csv : Nome do arquivo CSV na pasta storage/app/imports}
                            {--dry-run : Apenas simular, não salvar no banco}';

    protected $description = 'Importa/atualiza produtos a partir de um arquivo CSV';

    public function handle()
    {
        $filename = $this->option('file');
        $dryRun = $this->option('dry-run');
        
        $path = storage_path("app/imports/{$filename}");
        
        // Criar diretório se não existir
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
            $this->info("📁 Diretório criado: " . dirname($path));
        }
        
        if (!file_exists($path)) {
            $this->error("Arquivo não encontrado: {$path}");
            $this->info("Coloque o arquivo em: storage/app/imports/{$filename}");
            $this->info("");
            $this->info("Comando para copiar o arquivo:");
            $this->info("  cp ~/Downloads/Produtos.csv storage/app/imports/");
            return 1;
        }

        $this->info("📂 Lendo arquivo: {$filename}");
        $this->info("Modo: " . ($dryRun ? 'SIMULAÇÃO (dry-run)' : 'EXECUÇÃO REAL'));
        $this->info("");

        // Ler CSV
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';');
        
        $total = 0;
        $atualizados = 0;
        $erros = 0;
        $pular = 0;

        $this->info("Processando...");
        $bar = $this->output->createProgressBar();
        $bar->start();

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            $total++;

            try {
                // 🔧 CORREÇÃO: Converter valores monetários corretamente
                $valorCompra = $this->parseMoney($data['valor_compra'] ?? 0);
                $valorAtacado = $this->parseMoney($data['valor_atacado'] ?? 0);
                $valorCusto = $this->parseMoney($data['valor_custo'] ?? 0);
                $valorUnitario = $this->parseMoney($data['valor_unitario'] ?? 0);
                $ipi = $data['ipi'] && $data['ipi'] !== 'NULL' && $data['ipi'] !== '' ? $this->parseMoney($data['ipi']) : 9.75;
                $margem = $data['margem_lucro'] && $data['margem_lucro'] !== 'NULL' && $data['margem_lucro'] !== '' ? $this->parseMoney($data['margem_lucro']) : null;
                $percentualCusto = $this->parseMoney($data['percentual_custo'] ?? 0);

                $produto = Produto::find($data['id']);
                
                if (!$produto) {
                    $pular++;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $this->line("ID: {$data['id']} - {$data['descricao']}");
                    $this->line("  Compra: {$data['valor_compra']} -> " . number_format($valorCompra, 2, ',', '.'));
                    $this->line("  Atacado: {$data['valor_atacado']} -> " . number_format($valorAtacado, 2, ',', '.'));
                    $this->line("");
                    $atualizados++;
                    $bar->advance();
                    continue;
                }

                // Atualizar produto
                $produto->valor_compra = $valorCompra;
                $produto->valor_atacado = $valorAtacado;
                $produto->valor_custo = $valorCusto;
                $produto->valor_unitario = $valorUnitario;
                $produto->ipi = $ipi;
                $produto->margem_lucro = $margem;
                $produto->percentual_custo = $percentualCusto;
                $produto->disponibilidade = $data['disponibilidade'] ?? 'DISPONIVEL';
                $produto->ativo = ($data['ativo'] ?? 1) == 1;
                $produto->quantidade = (int) ($data['quantidade'] ?? 0);
                $produto->referencia = $data['referencia'] ?? null;
                $produto->descricao = $data['descricao'] ?? null;
                $produto->categoria = $data['categoria'] ?? null;
                $produto->slug = $data['slug'] ?? null;
                $produto->estoque_minimo = (int) ($data['estoque_minimo'] ?? 5);
                $produto->destaque = ($data['destaque'] ?? 0) == 1;
                $produto->novo = ($data['novo'] ?? 0) == 1;
                $produto->mais_vendido = ($data['mais_vendido'] ?? 0) == 1;
                
                if (!empty($data['data_compra']) && $data['data_compra'] !== 'NULL') {
                    $produto->data_compra = date('Y-m-d', strtotime($data['data_compra']));
                }

                // Recalcular preços se necessário
                if ($valorCompra > 0) {
                    $resultados = $produto->calcularTodosPrecos([
                        'valor_compra' => $valorCompra,
                        'margem_lucro' => $margem ?? 80,
                        'ipi' => $ipi ?? 9.75,
                    ]);
                    $produto->valor_custo = $resultados['valor_custo'];
                    $produto->valor_atacado = $resultados['valor_atacado'];
                    $produto->percentual_custo = $resultados['percentual_custo'];
                }

                $produto->save();
                $atualizados++;
                $bar->advance();

            } catch (\Exception $e) {
                $erros++;
                $this->error(" Erro no produto ID {$data['id']}: " . $e->getMessage());
                $bar->advance();
            }
        }

        fclose($handle);
        $bar->finish();

        $this->info('');
        $this->info('');
        $this->info('✅ IMPORTAÇÃO FINALIZADA!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Total de produtos no CSV: {$total}");
        $this->info("✅ Produtos atualizados: {$atualizados}");
        $this->info("⚠️  Produtos não encontrados: {$pular}");
        $this->info("❌ Erros: {$erros}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($dryRun) {
            $this->info("💡 Modo simulação: Nenhum dado foi alterado no banco.");
            $this->info("   Remova a opção --dry-run para executar realmente.");
        }
    }

    /**
     * Converte valor monetário do CSV para float
     * Exemplos: "0.57" -> 0.57, "1.27" -> 1.27, "NULL" -> 0
     */
    private function parseMoney($value): float
    {
        // Se for null, vazio ou NULL, retorna 0
        if ($value === null || $value === '' || $value === 'NULL' || $value === 'null') {
            return 0.0;
        }

        // Remove espaços em branco
        $value = trim($value);
        
        // Se tiver vírgula, pode ser formato brasileiro (1.719,00)
        if (strpos($value, ',') !== false) {
            // Remove pontos de milhar e troca vírgula por ponto
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        
        // Converte para float
        return (float) $value;
    }
}