#!/bin/bash

echo "🔧 Corrigindo ProdutoSeeder para tratar caracteres especiais..."

cat > database/seeders/ProdutoSeederFixed.php << 'PHP'
<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProdutoSeederFixed extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando importação de produtos (FIXED)...');
        
        $path = base_path('Produtos.csv');
        
        if (!File::exists($path)) {
            $this->command->error("❌ Arquivo não encontrado: {$path}");
            return;
        }

        // Usar ; como delimitador
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ';');
        
        if (!$headers) {
            $this->command->error("❌ Erro ao ler cabeçalho do CSV");
            fclose($handle);
            return;
        }

        // Normalizar cabeçalhos
        $headers = array_map(function($h) {
            return trim(str_replace(['"', "'"], '', $h));
        }, $headers);

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

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line++;
            
            if (count($row) < 5) {
                $pulados++;
                $bar->advance();
                continue;
            }
            
            try {
                // Criar array associativo
                $record = [];
                foreach ($headers as $index => $header) {
                    $value = $row[$index] ?? '';
                    // Limpar caracteres especiais
                    $value = trim($value);
                    $value = $this->sanitizeString($value);
                    $record[$header] = $value;
                }
                
                // Mapear campos
                $categoria = $record['categoria'] ?? '';
                $referencia = $record['referencia'] ?? '';
                $descricao = $record['descricao'] ?? '';
                $tipo = $record['tipo'] ?? '';
                
                // 🔥 CORREÇÃO: Limpar o campo 'tipo' que está causando erro
                $tipo = $this->sanitizeTipo($tipo);
                
                $quantidade = intval($record['quantidade'] ?? 0);
                $estoque = intval($record['estoque'] ?? 0);
                $status = $record['status'] ?? 'ativo';
                $estoque_minimo = intval($record['estoque_minimo'] ?? 5);
                
                $valor_atacado = $this->parseCurrency($record['valor_atacado'] ?? '');
                $valor_compra = $this->parseCurrency($record['valor_compra'] ?? '');
                $valor_custo = $this->parseCurrency($record['valor_custo'] ?? '');
                $preco_promocional = $this->parseCurrency($record['preco_promocional'] ?? '');
                $ipi = $this->parsePercentage($record['ipi'] ?? '');
                $margem_lucro = $this->parsePercentage($record['margem_lucro'] ?? '');
                $percentual_custo = $this->parsePercentage($record['percentual_custo'] ?? '');
                
                $ativo = 1;
                if (isset($record['ativo'])) {
                    $ativo = in_array(strtolower($record['ativo']), ['1', 'true', 'sim', 's', 'yes', 'y']) ? 1 : 0;
                }
                
                $destaque = 0;
                if (isset($record['destaque'])) {
                    $destaque = in_array(strtolower($record['destaque']), ['1', 'true', 'sim', 's', 'yes', 'y']) ? 1 : 0;
                }
                
                $novo = 0;
                if (isset($record['novo'])) {
                    $novo = in_array(strtolower($record['novo']), ['1', 'true', 'sim', 's', 'yes', 'y']) ? 1 : 0;
                }
                
                $mais_vendido = 0;
                if (isset($record['mais_vendido'])) {
                    $mais_vendido = in_array(strtolower($record['mais_vendido']), ['1', 'true', 'sim', 's', 'yes', 'y']) ? 1 : 0;
                }
                
                $visualizacoes = intval($record['visualizacoes'] ?? 0);
                $imagem = $record['imagem'] ?? '';
                $data_compra = $this->parseDate($record['data_compra'] ?? '');
                $created_at = $record['created_at'] ?? now();
                $updated_at = $record['updated_at'] ?? now();
                
                // Validar referência
                if (empty($referencia) || $referencia === 'referencia' || $referencia === 'Referência') {
                    $pulados++;
                    $bar->advance();
                    continue;
                }
                
                // Verificar se já existe
                if (Produto::where('referencia', $referencia)->exists()) {
                    $bar->advance();
                    continue;
                }
                
                // 🔥 CORREÇÃO: Limpar descrição
                $descricao = $this->sanitizeString($descricao);
                if (empty($descricao)) {
                    $descricao = $referencia;
                }
                
                // Criar slug
                $slug = $record['slug'] ?? '';
                if (empty($slug)) {
                    $slug = Str::slug($referencia . '-' . substr($descricao, 0, 30));
                }
                
                // 🔥 CORREÇÃO: Validar disponibilidade
                $disponibilidade = $record['disponibilidade'] ?? 'DISPONIVEL';
                $disponibilidade = $this->validateDisponibilidade($disponibilidade);
                
                // Criar produto
                $produto = new Produto();
                $produto->categoria_id = null;
                $produto->categoria = $categoria ?: 'GERAL';
                $produto->referencia = $referencia;
                $produto->descricao = $descricao;
                $produto->tipo = $tipo;
                $produto->disponibilidade = $disponibilidade;
                $produto->imagem = $imagem;
                $produto->galeria = null;
                $produto->slug = $slug;
                $produto->quantidade = $quantidade > 0 ? $quantidade : 0;
                $produto->estoque = $estoque;
                $produto->status = $status;
                $produto->estoque_minimo = $estoque_minimo > 0 ? $estoque_minimo : 5;
                $produto->valor_atacado = $valor_atacado;
                $produto->valor_compra = $valor_compra;
                $produto->valor_custo = $valor_custo;
                $produto->percentual_custo = $percentual_custo;
                $produto->valor_unitario = $valor_atacado;
                $produto->preco_promocional = $preco_promocional;
                $produto->ipi = $ipi;
                $produto->margem_lucro = $margem_lucro;
                $produto->ativo = $ativo;
                $produto->destaque = $destaque;
                $produto->novo = $novo;
                $produto->mais_vendido = $mais_vendido;
                $produto->visualizacoes = $visualizacoes;
                $produto->data_compra = $data_compra;
                $produto->created_at = $created_at;
                $produto->updated_at = $updated_at;
                
                $produto->save();
                $count++;
                
            } catch (\Exception $e) {
                $errors++;
                if ($errors < 10) {
                    $this->command->error("❌ Erro linha {$line}: " . $e->getMessage());
                }
            }

            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        
        $this->command->newLine(2);
        $this->command->info("✅ Importação concluída!");
        $this->command->info("📊 {$count} produtos importados com sucesso!");
        
        if ($pulados > 0) {
            $this->command->warn("⏭️  {$pulados} linhas puladas");
        }
        
        if ($errors > 0) {
            $this->command->error("❌ {$errors} erros encontrados");
        }
        
        $total = Produto::count();
        $this->command->info("📊 Total de produtos no banco: $total");
    }

    private function sanitizeString($value): string
    {
        if (empty($value)) return '';
        
        // Remover caracteres de controle
        $value = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $value);
        
        // Normalizar espaços
        $value = preg_replace('/\s+/', ' ', $value);
        
        return trim($value);
    }

    private function sanitizeTipo($value): string
    {
        if (empty($value)) return '';
        
        // Remover caracteres especiais problemáticos
        $value = preg_replace('/[^\p{L}\p{N}\s\/\-\.]/u', '', $value);
        
        // Limpar caracteres específicos que causam erro
        $value = str_replace(['�', '�', '�'], '', $value);
        
        // Limitar tamanho
        if (strlen($value) > 100) {
            $value = substr($value, 0, 100);
        }
        
        return trim($value);
    }

    private function validateDisponibilidade(string $value): string
    {
        $value = strtoupper(trim($value));
        $valid = ['DISPONIVEL', 'DISPONÍVEL', 'INDISPONIVEL', 'INDISPONÍVEL', 'ESTOQUE_BAIXO', 'SOB_ENCOMENDA'];
        
        if (in_array($value, $valid)) {
            return $value === 'DISPONÍVEL' ? 'DISPONIVEL' : $value;
        }
        
        if (in_array($value, ['SIM', '1', 'S', 'TRUE', 'YES', 'Y'])) {
            return 'DISPONIVEL';
        }
        
        if (in_array($value, ['NÃO', 'NAO', '0', 'N', 'FALSE', 'NO'])) {
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
PHP

echo "✅ Seeder corrigido criado!"
echo ""
echo "📌 Para executar:"
echo "   php artisan db:seed --class=ProdutoSeederFixed --force"
