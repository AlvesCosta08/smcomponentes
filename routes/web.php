<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\Admin\ProdutoAdminController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\Admin\BannerController;
use Illuminate\Support\Facades\Route;

// ============================================
// ROTAS PÚBLICAS
// ============================================

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de produtos (públicas)
Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');
Route::get('/produtos/filtro/{status}', [ProdutoController::class, 'filtroDisponibilidade'])->name('produtos.filtro');
Route::get('/produtos/categoria/{categoria}', [ProdutoController::class, 'porCategoria'])->name('produtos.categoria');
Route::get('/produtos/buscar', [ProdutoController::class, 'buscar'])->name('produtos.buscar');
Route::get('/produtos/{slug}', [ProdutoController::class, 'show'])->name('produtos.show');

// ============================================
// ROTAS DO CARRINHO (PÚBLICAS)
// ============================================

Route::prefix('carrinho')->name('carrinho.')->group(function () {
    Route::get('/', [CarrinhoController::class, 'index'])->name('index');
    Route::post('/adicionar', [CarrinhoController::class, 'adicionar'])->name('adicionar');
    Route::delete('/remover/{item}', [CarrinhoController::class, 'remover'])->name('remover');
    Route::put('/atualizar/{item}', [CarrinhoController::class, 'atualizar'])->name('atualizar');
    Route::get('/count', [CarrinhoController::class, 'count'])->name('count');
    Route::delete('/limpar', [CarrinhoController::class, 'limpar'])->name('limpar');
});

// ============================================
// ROTAS DE AUTENTICAÇÃO
// ============================================

require __DIR__.'/auth.php';

// ============================================
// ROTAS PROTEGIDAS (AUTENTICADAS)
// ============================================

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // ROTAS DE CHECKOUT E PAGAMENTO
    // ============================================
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/processar', [CheckoutController::class, 'processar'])->name('processar');
        Route::get('/pix/{pedido}', [CheckoutController::class, 'pix'])->name('pix');
        Route::get('/boleto/{pedido}', [CheckoutController::class, 'boleto'])->name('boleto');
        Route::get('/cartao/{pedido}', [CheckoutController::class, 'cartao'])->name('cartao');
        Route::get('/sucesso/{pedido}', [CheckoutController::class, 'sucesso'])->name('sucesso');
        Route::get('/falha/{pedido}', [CheckoutController::class, 'falha'])->name('falha');
        Route::get('/pendente/{pedido}', [CheckoutController::class, 'pendente'])->name('pendente');
        Route::get('/pedidos', [CheckoutController::class, 'meusPedidos'])->name('pedidos');
        Route::get('/pedidos/{pedido}', [CheckoutController::class, 'detalhes'])->name('detalhes');
        Route::post('/pedidos/{pedido}/cancelar', [CheckoutController::class, 'cancelar'])->name('cancelar');
    });

    // ============================================
    // ROTAS DA WISHLIST
    // ============================================
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::get('/{id}', [WishlistController::class, 'show'])->name('show');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::put('/{id}', [WishlistController::class, 'update'])->name('update');
        Route::delete('/{id}', [WishlistController::class, 'destroy'])->name('destroy');
        Route::post('/adicionar', [WishlistController::class, 'adicionar'])->name('adicionar');
        Route::post('/remover', [WishlistController::class, 'remover'])->name('remover');
        Route::post('/verificar', [WishlistController::class, 'verificar'])->name('verificar');
        Route::post('/mover', [WishlistController::class, 'mover'])->name('mover');
    });
});

// ============================================
// ROTAS ADMIN
// ============================================

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [PedidoAdminController::class, 'index'])->name('index');
        Route::get('/export', [PedidoAdminController::class, 'export'])->name('export');
        Route::get('/relatorio', [PedidoAdminController::class, 'relatorio'])->name('relatorio');
        Route::get('/{pedido}', [PedidoAdminController::class, 'show'])->name('show');
        Route::put('/{pedido}/status', [PedidoAdminController::class, 'updateStatus'])->name('status');
        Route::delete('/{pedido}', [PedidoAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/', [ProdutoAdminController::class, 'index'])->name('index');
        Route::get('/create', [ProdutoAdminController::class, 'create'])->name('create');
        Route::post('/', [ProdutoAdminController::class, 'store'])->name('store');
        Route::get('/export', [ProdutoAdminController::class, 'export'])->name('export');
        Route::get('/{id}', [ProdutoAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProdutoAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProdutoAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProdutoAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/ajustar-estoque', [ProdutoAdminController::class, 'ajustarEstoque'])->name('ajustar-estoque');
    });

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioAdminController::class, 'index'])->name('index');
        Route::get('/create', [UsuarioAdminController::class, 'create'])->name('create');
        Route::post('/', [UsuarioAdminController::class, 'store'])->name('store');
        Route::get('/{usuario}', [UsuarioAdminController::class, 'show'])->name('show');
        Route::get('/{usuario}/edit', [UsuarioAdminController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [UsuarioAdminController::class, 'update'])->name('update');
        Route::delete('/{usuario}', [UsuarioAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{usuario}/toggle-status', [UsuarioAdminController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/restore/{id}', [UsuarioAdminController::class, 'restore'])->name('restore');
        Route::get('/{usuario}/historico', [UsuarioAdminController::class, 'historicoPedidos'])->name('historico');
    });

    Route::resource('banners', BannerController::class);
    Route::post('banners/reorder', [BannerController::class, 'reorder'])->name('banners.reorder');
    Route::post('banners/{banner}/toggle', [BannerController::class, 'toggleStatus'])->name('banners.toggle');

    Route::prefix('cache')->name('cache.')->group(function () {
        Route::get('/clear', [HomeController::class, 'clearCache'])->name('clear');
        Route::get('/clear-banners', [HomeController::class, 'clearBannerCache'])->name('clear-banners');
        Route::get('/clear-products', [HomeController::class, 'clearProductCache'])->name('clear-products');
        Route::get('/reload-banners', [HomeController::class, 'reloadBanners'])->name('reload-banners');
        Route::get('/clear-all', [HomeController::class, 'clearAllCache'])->name('clear-all');
    });

    Route::get('/clear-cache', [HomeController::class, 'clearCache'])->name('clear.cache');
});

// ============================================
// ROTAS DE DEBUG
// ============================================

if (app()->environment('local')) {
    Route::get('/debug-banners', function() {
        $banners = App\Models\Banner::ativo()->ordenado()->get();
        
        $result = [];
        foreach ($banners as $banner) {
            $result[] = [
                'id' => $banner->id,
                'titulo' => $banner->titulo,
                'imagem_campo' => $banner->imagem,
                'imagem_url' => $banner->imagem_url,
                'estilo_fundo' => $banner->estilo_fundo,
                'ativo' => $banner->ativo,
                'ordem' => $banner->ordem,
                'existe_no_storage' => \Illuminate\Support\Facades\Storage::disk('public')->exists($banner->imagem),
                'caminho_completo' => public_path('storage/' . $banner->imagem),
                'arquivo_existe' => file_exists(public_path('storage/' . $banner->imagem)),
            ];
        }
        
        return response()->json([
            'total' => $banners->count(),
            'banners' => $result
        ]);
    })->name('debug.banners');

    Route::get('/debug-cache', function() {
        $cachedBanners = \Illuminate\Support\Facades\Cache::get('banners_ativos');
        
        return response()->json([
            'cache_exists' => \Illuminate\Support\Facades\Cache::has('banners_ativos'),
            'cache_data' => $cachedBanners,
            'cache_keys' => $cachedBanners ? array_keys((array)$cachedBanners) : []
        ]);
    })->name('debug.cache');

    Route::get('/debug-storage', function() {
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('banners');
        
        return response()->json([
            'storage_path' => storage_path('app/public'),
            'public_path' => public_path('storage'),
            'files_in_storage' => $files,
            'link_exists' => is_link(public_path('storage')),
            'link_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
        ]);
    })->name('debug.storage');
}