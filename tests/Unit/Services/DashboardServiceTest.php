<?php

namespace Tests\Unit\Services;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardService();
    }

    /** @test */
    public function pode_obter_estatisticas_completas_do_dashboard()
    {
        // Usuários
        User::factory()->count(5)->create();
        User::factory()->create(['ativo' => false]);

        // Produtos
        Produto::factory()->count(8)->create(['ativo' => true]);
        Produto::factory()->count(2)->create(['ativo' => false]);
        Produto::factory()->count(3)->create([
            'ativo' => true,
            'quantidade' => 3,
        ]);

        // Pedidos
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 150.00,
            'created_at' => now()
        ]);
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 200.00,
            'created_at' => now()
        ]);
        Pedido::factory()->create(['status' => 'pendente']);
        Pedido::factory()->create(['status' => 'processando']);

        $stats = $this->dashboardService->getStats();

        // Verificar usuários
        $this->assertEquals(7, $stats['usuarios']['total']);
        $this->assertEquals(6, $stats['usuarios']['ativos']);
        $this->assertEquals(1, $stats['usuarios']['inativos']);

        // Verificar produtos
        $this->assertEquals(13, $stats['produtos']['total']);
        $this->assertEquals(11, $stats['produtos']['ativos']);
        $this->assertEquals(2, $stats['produtos']['inativos']);
        $this->assertCount(3, $stats['produtos']['estoque_baixo']);

        // Verificar pedidos
        $this->assertEquals(4, $stats['pedidos']['total']);
        $this->assertEquals(2, $stats['pedidos']['entregues']);
        $this->assertEquals(1, $stats['pedidos']['pendentes']);
        $this->assertEquals(1, $stats['pedidos']['processando']);

        // Verificar faturamento
        $this->assertEquals(350.00, $stats['faturamento']['total']);
    }

    /** @test */
    public function pode_obter_top_clientes()
    {
        $cliente1 = User::factory()->create();
        $cliente2 = User::factory()->create();
        $cliente3 = User::factory()->create();
        
        Pedido::factory()->create([
            'user_id' => $cliente1->id,
            'status' => 'entregue',
            'total' => 500.00
        ]);
        Pedido::factory()->create([
            'user_id' => $cliente1->id,
            'status' => 'entregue',
            'total' => 300.00
        ]);
        Pedido::factory()->create([
            'user_id' => $cliente2->id,
            'status' => 'entregue',
            'total' => 400.00
        ]);
        Pedido::factory()->create([
            'user_id' => $cliente3->id,
            'status' => 'entregue',
            'total' => 100.00
        ]);
        
        $stats = $this->dashboardService->getStats();
        $topClientes = $stats['clientes_top'];
        
        $this->assertCount(3, $topClientes);
        $this->assertEquals($cliente1->id, $topClientes[0]['id']);
        $this->assertEquals(800.00, (float) $topClientes[0]['total_gasto']);
        $this->assertEquals(2, $topClientes[0]['total_pedidos']);
    }

    /** @test */
    public function faturamento_do_mes_correto()
    {
        // Pedidos do mês atual
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 100.00,
            'created_at' => now()
        ]);
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 50.00,
            'created_at' => now()
        ]);
        
        // Pedido do mês passado
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 200.00,
            'created_at' => now()->subMonth()
        ]);
        
        $stats = $this->dashboardService->getStats();
        
        $this->assertEquals(150.00, $stats['faturamento']['mes']);
    }

    /** @test */
    public function faturamento_do_dia_correto()
    {
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 100.00,
            'created_at' => today()
        ]);
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 50.00,
            'created_at' => today()
        ]);
        Pedido::factory()->create([
            'status' => 'entregue',
            'total' => 200.00,
            'created_at' => yesterday()
        ]);
        
        $stats = $this->dashboardService->getStats();
        
        $this->assertEquals(150.00, $stats['faturamento']['dia']);
    }

    /** @test */
    public function vendas_mensais_ultimos_6_meses()
    {
        $meses = [
            now()->subMonths(5)->format('Y-m'),
            now()->subMonths(4)->format('Y-m'),
            now()->subMonths(3)->format('Y-m'),
            now()->subMonths(2)->format('Y-m'),
            now()->subMonths(1)->format('Y-m'),
            now()->format('Y-m'),
        ];

        foreach ($meses as $mes) {
            Pedido::factory()->create([
                'status' => 'entregue',
                'total' => 100.00,
                'created_at' => \Carbon\Carbon::parse($mes . '-01'),
            ]);
        }

        $stats = $this->dashboardService->getStats();
        $vendasMensais = $stats['vendas_mensais'];

        $this->assertCount(6, $vendasMensais);
        $this->assertEquals(100.00, $vendasMensais[0]['total']);
    }

    /** @test */
    public function pedidos_hoje_contados_corretamente()
    {
        Pedido::factory()->create(['created_at' => today()]);
        Pedido::factory()->create(['created_at' => today()]);
        Pedido::factory()->create(['created_at' => yesterday()]);
        
        $stats = $this->dashboardService->getStats();
        
        $this->assertEquals(2, $stats['pedidos']['hoje']);
    }

    /** @test */
    public function ultimos_pedidos_retornados_corretamente()
    {
        $pedidos = Pedido::factory()->count(15)->create();
        
        $stats = $this->dashboardService->getStats();
        $ultimos = $stats['pedidos']['ultimos'];
        
        $this->assertCount(10, $ultimos);
        $this->assertEquals($pedidos->last()->id, $ultimos->first()->id);
    }

    /** @test */
    public function media_de_pedidos_por_dia_calculada_corretamente()
    {
        // Criar pedidos nos últimos 30 dias
        for ($i = 0; $i < 30; $i++) {
            Pedido::factory()->create([
                'status' => 'entregue',
                'created_at' => now()->subDays($i),
            ]);
        }
        
        $stats = $this->dashboardService->getStats();
        
        // 30 pedidos / 30 dias = 1
        $this->assertEquals(1.0, $stats['faturamento']['media_dia']);
    }
}