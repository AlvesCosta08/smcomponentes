<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', [
            '--class' => 'RoleSeeder',
            '--force' => true,
        ]);

        $this->categoria = Categoria::factory()->create([
            'ativo' => true,
        ]);

        $this->admin = User::factory()->create();
    }

    /** @test */
    public function usuario_pode_listar_produtos()
    {
        Produto::factory()->count(5)->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->get(route('produtos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }

    /** @test */
    public function usuario_pode_ver_detalhes_de_um_produto()
    {
        $produto = Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
        ]);

        $response = $this->get(
            route('produtos.show', $produto->slug)
        );

        $response->assertStatus(200);
        $response->assertViewHas('produto');
    }

    /** @test */
    public function usuario_pode_buscar_produtos()
    {
        Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'descricao' => 'Produto Especial',
        ]);

        Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'descricao' => 'Outro Produto',
        ]);

        $response = $this->get(
            route('produtos.buscar', ['q' => 'Especial'])
        );

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }

    /** @test */
    public function usuario_pode_filtrar_produtos_por_categoria()
    {
        $categoria = Categoria::factory()->create([
            'ativo' => true,
        ]);

        Produto::factory()->count(3)->create([
            'categoria_id' => $categoria->id,
            'categoria' => $categoria->nome,
        ]);

        $response = $this->get(
            route('produtos.categoria', $categoria->slug)
        );

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }

    /** @test */
    public function usuario_pode_ver_produtos_em_promocao()
    {
        Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'preco_promocional' => 50.00,
            'valor_atacado' => 100.00,
            'descricao' => 'Produto em Promoção 1',
        ]);

        Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'preco_promocional' => 30.00,
            'valor_atacado' => 60.00,
            'descricao' => 'Produto em Promoção 2',
        ]);

        Produto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'preco_promocional' => null,
            'descricao' => 'Produto Sem Promoção',
        ]);

        $response = $this->get(
            route('produtos.filtro', 'ofertas')
        );

        $response->assertStatus(200);
        $response->assertViewHas('produtos');
    }
}

