<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->categoria = Categoria::factory()->create();
        $this->admin = User::factory()->create();
    }

    /** @test */
    public function usuario_pode_listar_produtos()
    {
        Produto::factory()->count(5)->create();

        $response = $this->get('/produtos');

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_um_produto()
    {
        $produto = Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->get("/produtos/{$produto->slug}");

        $response->assertStatus(200);
        $response->assertViewHas('produto');
    }

    /** @test */
    public function usuario_pode_buscar_produtos()
    {
        Produto::factory()->create(['descricao' => 'Produto Especial']);
        Produto::factory()->create(['descricao' => 'Outro Produto']);

        $response = $this->get('/produtos/buscar?q=Especial');

        $response->assertStatus(200);
        $response->assertSee('Produto Especial');
    }

    /** @test */
    public function usuario_pode_filtrar_produtos_por_categoria()
    {
        $categoria1 = Categoria::factory()->create(['nome' => 'Categoria 1']);
        $categoria2 = Categoria::factory()->create(['nome' => 'Categoria 2']);
        
        Produto::factory()->create([
            'categoria_id' => $categoria1->id,
            'descricao' => 'Produto Categoria 1',
        ]);
        
        Produto::factory()->create([
            'categoria_id' => $categoria2->id,
            'descricao' => 'Produto Categoria 2',
        ]);

        $response = $this->get("/produtos/categoria/{$categoria1->id}");

        $response->assertStatus(200);
        $response->assertSee('Produto Categoria 1');
    }

    /** @test */
    public function usuario_pode_ver_produtos_em_promocao()
    {
        Produto::factory()->comPromocao()->count(3)->create();
        Produto::factory()->count(2)->create(['preco_promocional' => null]);

        $response = $this->get('/produtos/filtro/ofertas');

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }
}
