<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pode_criar_uma_categoria()
    {
        $categoria = Categoria::factory()->create([
            'nome' => 'Eletrônicos'
        ]);

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nome' => 'Eletrônicos'
        ]);
    }

    /** @test */
    public function categoria_tem_slug_unico()
    {
        Categoria::factory()->create(['slug' => 'eletronicos']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Categoria::factory()->create(['slug' => 'eletronicos']);
    }

    /** @test */
    public function categoria_pode_ter_varios_produtos()
    {
        $categoria = Categoria::factory()->create();
        Produto::factory()->count(3)->create([
            'categoria_id' => $categoria->id
        ]);
        
        $this->assertCount(3, $categoria->produtos);
    }

    /** @test */
    public function categoria_pode_ser_ativada_ou_desativada()
    {
        $categoriaAtiva = Categoria::factory()->create(['ativo' => true]);
        $categoriaInativa = Categoria::factory()->create(['ativo' => false]);
        
        $this->assertTrue($categoriaAtiva->ativo);
        $this->assertFalse($categoriaInativa->ativo);
    }
}