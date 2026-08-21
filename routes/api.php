<?php

use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ProdutoApiController;
use App\Http\Controllers\Api\PedidoApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================
// LÓGICA 1: WEBHOOKS (ACESSO PÚBLICO - SEM AUTENTICAÇÃO)
// ============================================================

Route::prefix('webhooks')->name('webhook.')->group(function () {
    Route::post('/mercadopago', [WebhookController::class, 'mercadopago'])->name('mercadopago');
    Route::post('/{gateway}', [WebhookController::class, 'handle'])
        ->where('gateway', 'mercadopago|pagseguro|stripe|paypal')
        ->name('generic');
});

// ============================================================
// LÓGICA 2: HEALTH CHECK (SEM AUTENTICAÇÃO)
// ============================================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
        'version' => '1.0.0',
    ]);
})->name('health');

Route::get('/status', function () {
    return response()->json([
        'app_name' => config('app.name'),
        'environment' => app()->environment(),
        'debug' => config('app.debug'),
        'timezone' => config('app.timezone'),
        'locale' => app()->getLocale(),
    ]);
})->name('status');

// ============================================================
// LÓGICA 3: API VERSIONADA (v1)
// ============================================================

Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // --- 3.1 Endpoints Públicos (Produtos) ---
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/', [ProdutoApiController::class, 'index'])->name('index');
        Route::get('/destaques', [ProdutoApiController::class, 'destaques'])->name('destaques');
        Route::get('/ofertas', [ProdutoApiController::class, 'ofertas'])->name('ofertas');
        Route::get('/novos', [ProdutoApiController::class, 'novos'])->name('novos');
        Route::get('/mais-vendidos', [ProdutoApiController::class, 'maisVendidos'])->name('mais-vendidos');
        Route::get('/{slug}', [ProdutoApiController::class, 'show'])->name('show');
    });

    // --- 3.2 Endpoints Autenticados ---
    Route::middleware('auth:sanctum')->group(function () {
        
        // Usuário
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');
        
        // Cliente
        Route::prefix('cliente')->name('cliente.')->group(function () {
            Route::get('/perfil', fn(Request $request) => $request->user())->name('perfil');
            Route::get('/pedidos', [PedidoApiController::class, 'meusPedidos'])->name('pedidos');
            Route::get('/pedidos/{pedido}', [PedidoApiController::class, 'show'])->name('pedidos.show');
            Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist');
        });
        
        // Admin
        Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'apiStats'])
                ->name('dashboard');
            Route::get('/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'apiStats'])
                ->name('stats');
            Route::get('/pedidos', [PedidoApiController::class, 'index'])->name('pedidos');
            Route::get('/produtos', [ProdutoApiController::class, 'adminIndex'])->name('produtos');
            Route::get('/usuarios', [UserApiController::class, 'index'])->name('usuarios');
        });
    });
});

// ============================================================
// LÓGICA 4: FERRAMENTAS DE DEBUG (APENAS DESENVOLVIMENTO)
// ============================================================

// CORRIGIDO: Rotas de debug carregadas apenas em web.php para evitar conflito
// if (app()->environment('local')) {
//     require __DIR__ . '/debug.php';
// }