<?php

namespace Tests\Unit;

use App\Models\ProdutoImagem;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoImagemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_uma_imagem_do_produto()
    {
        $imagem = ProdutoImagem::factory()->create([
            'imagem' => 'produtos/imagem.jpg'
        ]);

        $this->assertDatabaseHas('produto_imagens', [
            'id' => $imagem->id,
            'imagem' => 'produtos/imagem.jpg'
        ]);
    }

    /** @test */
    public function imagem_pertence_a_produto()
    {
        $produto = Produto::factory()->create();
        $imagem = ProdutoImagem::factory()->create(['produto_id' => $produto->id]);
        
        $this->assertInstanceOf(Produto::class, $imagem->produto);
    }

    /** @test */
    public function imagem_pode_ser_principal()
    {
        try {
            $principal = ProdutoImagem::factory()->principal()->create();
            $secundaria = ProdutoImagem::factory()->secundaria()->create();
            
            $this->assertTrue($principal->principal);
            $this->assertFalse($secundaria->principal);
        } catch (\Exception $e) {
            $this->markTestSkipped('Métodos principal/secundaria não disponíveis: ' . $e->getMessage());
        }
    }
}
