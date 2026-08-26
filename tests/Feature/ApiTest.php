<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    /** @test */
    public function api_retorna_lista_de_produtos()
    {
        Produto::factory()->count(5)->create();

        $response = $this->getJson('/api/produtos');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'descricao', 'slug', 'valor_atacado']
            ],
            'meta' => ['total', 'current_page']
        ]);
    }

    /** @test */
    public function api_retorna_detalhes_do_produto()
    {
        $categoria = Categoria::factory()->create();
        $produto = Produto::factory()->create([
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->getJson("/api/produtos/{$produto->slug}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $produto->id,
            'descricao' => $produto->descricao,
            'slug' => $produto->slug,
        ]);
    }

    /** @test */
    public function api_retorna_produtos_em_promocao()
    {
        Produto::factory()->comPromocao()->count(3)->create();
        Produto::factory()->count(2)->create(['preco_promocional' => null]);

        $response = $this->getJson('/api/produtos/ofertas');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function api_busca_produtos_por_termo()
    {
        Produto::factory()->create(['descricao' => 'Produto Especial']);
        Produto::factory()->create(['descricao' => 'Outro Produto']);

        $response = $this->getJson('/api/produtos/buscar?q=Especial');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function api_retorna_categorias()
    {
        Categoria::factory()->count(5)->create();

        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nome', 'slug']
            ]
        ]);
    }

    /** @test */
    public function api_requer_autenticacao_para_pedidos()
    {
        $response = $this->getJson('/api/pedidos');
        $response->assertStatus(401);
    }

    /** @test */
    public function api_usuario_autenticado_pode_ver_pedidos()
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->getJson('/api/pedidos');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
    }

    /** @test */
    public function api_retorna_produto_nao_encontrado()
    {
        $response = $this->getJson('/api/produtos/999999');
        $response->assertStatus(404);
    }
}
