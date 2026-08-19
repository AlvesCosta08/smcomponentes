<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando importação de produtos...');
        
        $path = base_path('Produtos.csv');
        
        if (!File::exists($path)) {
            $this->command->error("❌ Arquivo não encontrado: {$path}");
            return;
        }

        // Usar ; como delimitador
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ';'); // ← MUDADO para ;
        
        if (!$headers) {
            $this->command->error("❌ Erro ao ler cabeçalho do CSV");
            fclose($handle);
            return;
        }

        $this->command->info("📋 Cabeçalhos: " . implode('; ', $headers));

        $count = 0;
        $errors = 0;
        $pulados = 0;
        $line = 1;

        // Barra de progresso
        $totalLinhas = $this->countLines($path) - 1;
        $this->command->info("📊 Total de produtos no CSV: $totalLinhas");
        $bar = $this->command->getOutput()->createProgressBar($totalLinhas);
        $bar->start();

        while (($row = fgetcsv($handle, 0, ';')) !== false) { // ← MUDADO para ;
            $line++;
            
            // Pular linhas vazias
            if (count($row) < 5) {
                $pulados++;
                $bar->advance();
                continue;
            }
            
            try {
                // Criar array associativo com os cabeçalhos
                $record = [];
                foreach ($headers as $index => $header) {
                    $record[trim($header)] = $row[$index] ?? '';
                }
                
                // Mapear os campos do CSV
                $categoria = trim($record['categoria'] ?? '');
                $categoria_id = !empty($record['categoria_id']) && $record['categoria_id'] !== 'NULL' ? $record['categoria_id'] : null;
                $referencia = trim($record['referencia'] ?? '');
                $descricao = trim($record['descricao'] ?? '');
                $tipo = trim($record['tipo'] ?? '');
                $disponibilidade = trim($record['disponibilidade'] ?? '');
                $imagem = trim($record['imagem'] ?? '');
                $galeria = trim($record['galeria'] ?? '');
                $slug = trim($record['slug'] ?? '');
                $quantidade = intval($record['quantidade'] ?? 0);
                $estoque = intval($record['estoque'] ?? 0);
                $status = trim($record['status'] ?? 'ativo');
                $estoque_minimo = intval($record['estoque_minimo'] ?? 5);
                $valor_atacado = $this->parseCurrency($record['valor_atacado'] ?? '');
                $valor_compra = $this->parseCurrency($record['valor_compra'] ?? '');
                $valor_unitario = $this->parseCurrency($record['valor_unitario'] ?? '');
                $valor_custo = $this->parseCurrency($record['valor_custo'] ?? '');
                $preco_promocional = $this->parseCurrency($record['preco_promocional'] ?? '');
                $ipi = $this->parsePercentage($record['ipi'] ?? '');
                $percentual_custo = $this->parsePercentage($record['percentual_custo'] ?? '');
                $margem_lucro = $this->parsePercentage($record['margem_lucro'] ?? '');
                $ativo = intval($record['ativo'] ?? 1);
                $destaque = intval($record['destaque'] ?? 0);
                $novo = intval($record['novo'] ?? 0);
                $mais_vendido = intval($record['mais_vendido'] ?? 0);
                $visualizacoes = intval($record['visualizacoes'] ?? 0);
                $rating = floatval(str_replace(',', '.', $record['rating'] ?? 0));
                $total_avaliacoes = intval($record['total_avaliacoes'] ?? 0);
                $data_compra = $this->parseDate($record['data_compra'] ?? '');
                $created_at = $record['created_at'] ?? now();
                $updated_at = $record['updated_at'] ?? now();
                
                // Validar referência
                if (empty($referencia)) {
                    $pulados++;
                    $bar->advance();
                    continue;
                }
                
                // Verificar se já existe
                if (Produto::where('referencia', $referencia)->exists()) {
                    $bar->advance();
                    continue;
                }
                
                // Criar produto
                $produto = new Produto();
                $produto->categoria_id = $categoria_id;
                $produto->categoria = $categoria ?: 'GERAL';
                $produto->referencia = $referencia;
                $produto->descricao = $descricao ?: $referencia;
                $produto->tipo = $tipo;
                $produto->disponibilidade = $this->validateDisponibilidade($disponibilidade);
                $produto->imagem = $imagem;
                $produto->galeria = $galeria !== 'NULL' ? $galeria : null;
                $produto->slug = $slug ?: Str::slug($referencia . '-' . substr($descricao, 0, 30));
                $produto->quantidade = $quantidade;
                $produto->estoque = $estoque;
                $produto->status = $status;
                $produto->estoque_minimo = $estoque_minimo;
                $produto->valor_atacado = $valor_atacado;
                $produto->valor_compra = $valor_compra;
                $produto->valor_unitario = $valor_unitario;
                $produto->valor_custo = $valor_custo;
                $produto->preco_promocional = $preco_promocional;
                $produto->ipi = $ipi;
                $produto->percentual_custo = $percentual_custo;
                $produto->margem_lucro = $margem_lucro;
                $produto->ativo = $ativo;
                $produto->destaque = $destaque;
                $produto->novo = $novo;
                $produto->mais_vendido = $mais_vendido;
                $produto->visualizacoes = $visualizacoes;
                $produto->rating = $rating;
                $produto->total_avaliacoes = $total_avaliacoes;
                $produto->data_compra = $data_compra;
                $produto->created_at = $created_at;
                $produto->updated_at = $updated_at;
                
                $produto->save();
                $count++;
                
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("❌ Erro linha {$line}: " . $e->getMessage());
            }

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        
        $this->command->newLine(2);
        $this->command->info("✅ Importação concluída!");
        $this->command->info("📊 {$count} produtos importados com sucesso!");
        
        if ($pulados > 0) {
            $this->command->warn("⏭️  {$pulados} linhas puladas (sem referência)");
        }
        
        if ($errors > 0) {
            $this->command->error("❌ {$errors} erros encontrados");
        }
        
        // Mostrar total no banco
        $total = Produto::count();
        $this->command->info("📊 Total de produtos no banco: $total");
    }

    private function validateDisponibilidade(string $value): string
    {
        $value = strtoupper(trim($value));
        $valid = ['DISPONIVEL', 'DISPONÍVEL', 'INDISPONIVEL', 'INDISPONÍVEL', 'EST.BAIXO', 'SOB_ENCOMENDA'];
        
        if (in_array($value, $valid)) {
            return $value === 'DISPONÍVEL' ? 'DISPONIVEL' : $value;
        }
        
        if ($value === 'SIM' || $value === '1' || $value === 'S') {
            return 'DISPONIVEL';
        }
        
        if ($value === 'NÃO' || $value === 'NAO' || $value === '0' || $value === 'N') {
            return 'INDISPONIVEL';
        }
        
        return 'INDISPONIVEL';
    }

    private function parseCurrency($value): ?float
    {
        $value = trim($value);
        if (empty($value) || $value === '0' || $value === '-' || $value === '' || $value === 'NULL') {
            return null;
        }
        
        $value = str_replace(['R$', ' ', '.', '(', ')'], '', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
        
        if (is_numeric($value) && $value > 0) {
            return (float) $value;
        }
        return null;
    }

    private function parsePercentage($value): ?float
    {
        $value = trim($value);
        if (empty($value) || $value === '-' || $value === '' || $value === 'NULL') {
            return null;
        }
        
        $value = str_replace(['%', ' '], '', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
        
        if (is_numeric($value) && $value > 0) {
            return (float) $value;
        }
        return null;
    }

    private function parseDate($value): ?string
    {
        $value = trim($value);
        if (empty($value) || $value === '-' || $value === '' || $value === 'NULL') {
            return null;
        }
        
        $formats = ['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }

    private function countLines($file)
    {
        $linecount = 0;
        $handle = fopen($file, 'r');
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }
        fclose($handle);
        return $linecount;
    }
}