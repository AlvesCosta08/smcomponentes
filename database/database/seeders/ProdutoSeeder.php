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

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        
        if (!$headers) {
            $this->command->error("❌ Erro ao ler cabeçalho do CSV");
            fclose($handle);
            return;
        }

        $this->command->info("📋 Cabeçalhos: " . implode(', ', $headers));

        $count = 0;
        $errors = 0;
        $line = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $line++;
            
            if (count($row) < 2 || empty(trim($row[0] ?? ''))) {
                continue;
            }
            
            try {
                $record = [];
                foreach ($headers as $index => $header) {
                    $record[$header] = $row[$index] ?? '';
                }
                
                $categoria = trim($record['Categoria'] ?? '');
                $referencia = trim($record['Refefencia_Produto'] ?? '');
                $imagem = trim($record['Imagem_Produto'] ?? '');
                $descricao = trim($record['Descricao'] ?? '');
                $tipo = trim($record['Tipo'] ?? '');
                $valor_atacado = $this->parseCurrency($record['Valor_Atacado'] ?? '');
                $disponibilidade = trim($record['Disponibilidade'] ?? '');
                $quantidade = $this->parseInteger($record['Quantidade'] ?? 0);
                $valor_compra = $this->parseCurrency($record['Valor_Compra'] ?? '');
                $data_compra = $this->parseDate($record['Data'] ?? '');
                $ipi = $this->parsePercentage($record['IPI'] ?? '');
                $percentual_custo = $this->parsePercentage($record['Percentual_custo'] ?? '');
                $valor_custo = $this->parseCurrency($record['Valor_Custo'] ?? '');
                $valor_unitario = $this->parseCurrency($record['Valor_Unitario'] ?? '');
                
                if (empty($referencia)) {
                    $this->command->warn("⚠️ Linha {$line}: Referência vazia, pulando...");
                    continue;
                }
                
                if (Produto::where('referencia', $referencia)->exists()) {
                    $this->command->warn("⚠️ Linha {$line}: Produto {$referencia} já existe, pulando...");
                    continue;
                }
                
                $produto = new Produto();
                $produto->categoria = $categoria ?: 'GERAL';
                $produto->referencia = $referencia;
                $produto->descricao = $descricao ?: $referencia;
                $produto->tipo = $tipo;
                $produto->imagem = $imagem;
                $produto->valor_atacado = $valor_atacado;
                $produto->disponibilidade = $this->validateDisponibilidade($disponibilidade);
                $produto->quantidade = $quantidade;
                $produto->valor_compra = $valor_compra;
                $produto->data_compra = $data_compra;
                $produto->ipi = $ipi;
                $produto->percentual_custo = $percentual_custo;
                $produto->valor_custo = $valor_custo;
                $produto->valor_unitario = $valor_unitario;
                $produto->slug = Str::slug($referencia . '-' . substr($descricao, 0, 30));
                $produto->ativo = true;
                $produto->estoque_minimo = 5;
                
                $produto->save();
                $count++;
                
                if ($count % 10 === 0) {
                    $this->command->info("📊 {$count} produtos importados...");
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("❌ Erro linha {$line}: " . $e->getMessage());
            }
        }

        fclose($handle);
        
        $this->command->info("\n✅ Importação concluída!");
        $this->command->info("📊 {$count} produtos importados com sucesso!");
        
        if ($errors > 0) {
            $this->command->warn("⚠️ {$errors} erros encontrados");
        }
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
        if (empty($value) || $value === '0' || $value === '-' || $value === '') {
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

    private function parseInteger($value): int
    {
        $value = trim($value);
        if (empty($value) || $value === '-') {
            return 0;
        }
        $value = preg_replace('/[^\d]/', '', $value);
        return (int) $value;
    }

    private function parsePercentage($value): ?float
    {
        $value = trim($value);
        if (empty($value) || $value === '-' || $value === '') {
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
        if (empty($value) || $value === '-' || $value === '') {
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
}