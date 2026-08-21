<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FixProductPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dados do CSV para correção
        $products = [
            // id => [valor_compra, valor_atacado, ipi, margem]
            1628 => [17.19, 21.64, 9.75, 20.54],
            // Adicione mais produtos conforme necessário
        ];

        $count = 0;
        foreach ($products as $id => $data) {
            $produto = Produto::find($id);
            if ($produto) {
                $produto->valor_compra = $data[0];
                $produto->valor_atacado = $data[1];
                $produto->valor_custo = $data[0];
                $produto->ipi = $data[2];
                $produto->margem_lucro = $data[3];
                $produto->percentual_custo = round(($data[0] / $data[1]) * 100, 2);
                $produto->valor_unitario = $data[1];
                $produto->save();
                $count++;
                echo "Produto {$produto->id} - {$produto->descricao} corrigido!\n";
            }
        }

        echo "\n✅ Total de produtos corrigidos: {$count}\n";
    }
}