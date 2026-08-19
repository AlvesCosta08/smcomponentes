<?php

namespace Tests\Unit\Services;

use App\DTOs\OrderDTO;
use App\Exceptions\OutOfStockException;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use App\Repositories\PedidoRepository;
use App\Repositories\Contracts\PedidoItemRepositoryInterface;
use App\Repositories\Contracts\ProdutoRepositoryInterface;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Usar repositórios reais
        $pedidoRepository = new PedidoRepository();
        $produtoRepository = app(ProdutoRepositoryInterface::class);
        $pedidoItemRepository = app(PedidoItemRepositoryInterface::class);
        
        // StockService precisa do ProdutoRepository
        $stockService = new StockService($produtoRepository);
        
        $this->orderService = new OrderService(
            $pedidoRepository,
            $produtoRepository,
            $pedidoItemRepository,
            $stockService
        );
    }

    // ... resto dos testes
}