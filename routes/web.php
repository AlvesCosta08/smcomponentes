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
use App\Http\Controllers\Admin\CategoriaAdminController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// ============================================================
// LÓGICA 1: ROTAS PÚBLICAS (ACESSO LIVRE)
// ============================================================

// --- 1.1 Página Inicial ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- 1.2 Páginas Estáticas ---
Route::get('/termos', [HomeController::class, 'termos'])->name('termos');
Route::get('/privacidade', [HomeController::class, 'privacidade'])->name('privacidade');
Route::get('/contato', [HomeController::class, 'contato'])->name('contato');
Route::get('/sobre', [HomeController::class, 'sobre'])->name('sobre');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

// --- 1.3 Catálogo de Produtos ---
Route::prefix('produtos')->name('produtos.')->group(function () {
    Route::get('/', [ProdutoController::class, 'index'])->name('index');
    Route::get('/filtro/{status}', [ProdutoController::class, 'filtroDisponibilidade'])->name('filtro');
    Route::get('/categoria/{categoria}', [ProdutoController::class, 'porCategoria'])->name('categoria');
    Route::get('/buscar', [ProdutoController::class, 'buscar'])->name('buscar');
    Route::get('/{slug}', [ProdutoController::class, 'show'])->name('show');
});

// --- 1.4 Autenticação ---
require __DIR__ . '/auth.php';

// ============================================================
// LÓGICA 2: CARRINHO DE COMPRAS (PÚBLICO COM THROTTLE)
// ============================================================

Route::prefix('carrinho')->name('carrinho.')->middleware('throttle:30,1')->group(function () {
    Route::get('/', [CarrinhoController::class, 'index'])->name('index');
    Route::get('/count', [CarrinhoController::class, 'count'])->name('count');
    Route::get('/total', [CarrinhoController::class, 'total'])->name('total');
    Route::post('/adicionar', [CarrinhoController::class, 'adicionar'])->name('adicionar');
    Route::put('/atualizar/{item}', [CarrinhoController::class, 'atualizar'])->name('atualizar');
    Route::delete('/remover/{item}', [CarrinhoController::class, 'remover'])->name('remover');
    Route::delete('/limpar', [CarrinhoController::class, 'limpar'])->name('limpar');
});

// ============================================================
// LÓGICA 3: ÁREA DO CLIENTE (REQUER AUTENTICAÇÃO)
// ============================================================

Route::middleware(['auth'])->prefix('cliente')->name('cliente.')->group(function () {
    
    // --- 3.1 Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // --- 3.2 Perfil (UNIFICADO) ---
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/visualizar', [ProfileController::class, 'show'])->name('show');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/senha', [ProfileController::class, 'updatePassword'])->name('password');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/reativar', [ProfileController::class, 'reativar'])->name('reativar');
        Route::get('/historico', [ProfileController::class, 'historico'])->name('historico');
    });
    
    // --- 3.3 Pedidos ---
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [CheckoutController::class, 'meusPedidos'])->name('index');
        Route::get('/{pedido}', [CheckoutController::class, 'detalhes'])->name('detalhes');
        Route::post('/{pedido}/cancelar', [CheckoutController::class, 'cancelar'])->name('cancelar');
    });
    
    // --- 3.4 Wishlist ---
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

// ============================================================
// LÓGICA 4: CHECKOUT E PAGAMENTOS (REQUER AUTENTICAÇÃO)
// ============================================================

Route::middleware(['auth'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/processar', [CheckoutController::class, 'processar'])->name('processar');
    
    // ✅ ADICIONADO: Rotas de sucesso e falha
    Route::get('/sucesso/{pedido}', [CheckoutController::class, 'sucesso'])->name('sucesso');
    Route::get('/falha/{pedido}', [CheckoutController::class, 'falha'])->name('falha');
    
    Route::get('/pagamento/{pedido}/{metodo}', [CheckoutController::class, 'pagamento'])
        ->where('metodo', 'pix|boleto|cartao')
        ->name('pagamento');
    Route::get('/status/{pedido}/{status}', [CheckoutController::class, 'status'])
        ->where('status', 'sucesso|falha|pendente')
        ->name('status');
    Route::get('/pedidos', [CheckoutController::class, 'meusPedidos'])->name('pedidos');
});

// ============================================================
// LÓGICA 5: ROTAS LEGADO (COMPATIBILIDADE - REMOVER FUTURAMENTE)
// ============================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', fn() => redirect()->route('cliente.perfil.edit'))->name('profile.edit');
    Route::patch('/profile', fn() => redirect()->route('cliente.perfil.update'))->name('profile.update');
    Route::delete('/profile', fn() => redirect()->route('cliente.perfil.destroy'))->name('profile.destroy');
    Route::get('/dashboard', fn() => redirect()->route('cliente.dashboard'))->name('dashboard');
});

// ============================================================
// LÓGICA 6: ÁREA ADMINISTRATIVA (REQUER ROLE ADMIN)
// ============================================================

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // --- 6.1 Dashboard ---
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // --- 6.2 Pedidos ---
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [PedidoAdminController::class, 'index'])->name('index');
        Route::get('/export', [PedidoAdminController::class, 'export'])->name('export');
        Route::get('/relatorio', [PedidoAdminController::class, 'relatorio'])->name('relatorio');
        Route::get('/{pedido}', [PedidoAdminController::class, 'show'])->name('show');
        Route::put('/{pedido}/status', [PedidoAdminController::class, 'updateStatus'])->name('status');
        Route::delete('/{pedido}', [PedidoAdminController::class, 'destroy'])->name('destroy');
    });

    // --- 6.3 Produtos ---
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('/', [ProdutoAdminController::class, 'index'])->name('index');
        Route::get('/export', [ProdutoAdminController::class, 'export'])->name('export');
        Route::get('/create', [ProdutoAdminController::class, 'create'])->name('create');
        Route::post('/', [ProdutoAdminController::class, 'store'])->name('store');
        Route::get('/{id}', [ProdutoAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProdutoAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProdutoAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProdutoAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/ajustar-estoque', [ProdutoAdminController::class, 'ajustarEstoque'])->name('ajustar-estoque');
        Route::delete('/imagem/{id}', [ProdutoAdminController::class, 'removerImagem'])->name('remover-imagem');
        Route::post('/imagem/{id}/principal', [ProdutoAdminController::class, 'definirPrincipal'])->name('imagem-principal');
    });

    // --- 6.4 Usuários ---
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

    // --- 6.5 Banners ---
    Route::prefix('banners')->name('banners.')->group(function () {
        Route::get('/', [BannerController::class, 'index'])->name('index');
        Route::get('/create', [BannerController::class, 'create'])->name('create');
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::get('/{banner}', [BannerController::class, 'show'])->name('show');
        Route::get('/{banner}/edit', [BannerController::class, 'edit'])->name('edit');
        Route::put('/{banner}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [BannerController::class, 'reorder'])->name('reorder');
        Route::post('/{banner}/toggle', [BannerController::class, 'toggleStatus'])->name('toggle');
    });

    // --- 6.6 Cache ---
    Route::prefix('cache')->name('cache.')->group(function () {
        Route::get('/clear', [HomeController::class, 'clearCache'])->name('clear');
        Route::get('/clear-banners', [HomeController::class, 'clearBannerCache'])->name('clear-banners');
        Route::get('/clear-products', [HomeController::class, 'clearProductCache'])->name('clear-products');
        Route::get('/reload-banners', [HomeController::class, 'reloadBanners'])->name('reload-banners');
        Route::get('/clear-all', [HomeController::class, 'clearAllCache'])->name('clear-all');
    });

    // --- 6.7 Categorias ---
    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', [CategoriaAdminController::class, 'index'])->name('index');
        Route::get('/create', [CategoriaAdminController::class, 'create'])->name('create');
        Route::post('/', [CategoriaAdminController::class, 'store'])->name('store');
        Route::get('/{categoria}/edit', [CategoriaAdminController::class, 'edit'])->name('edit');
        Route::put('/{categoria}', [CategoriaAdminController::class, 'update'])->name('update');
        Route::delete('/{categoria}', [CategoriaAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{categoria}/toggle', [CategoriaAdminController::class, 'toggleStatus'])->name('toggle');
        Route::post('/reorder', [CategoriaAdminController::class, 'reorder'])->name('reorder');
    });
});

// ============================================================
// LÓGICA 7: FERRAMENTAS DE DEBUG (APENAS DESENVOLVIMENTO)
// ============================================================

if (app()->environment('local')) {
    require __DIR__ . '/debug.php';
}

// ============================================================
// LÓGICA 8: ROTAS PARA IMAGENS (CORRIGIDAS)
// ============================================================

/**
 * ROTAS DE IMAGENS
 * 
 * As imagens são servidas pelo ImageController que:
 * - Busca em múltiplas pastas (produtos/, uploads/, images/, etc)
 * - Gera placeholder quando não encontra
 * - Aplica cache de 1 ano
 * - Suporta redimensionamento via URL
 */

// Rota principal para servir imagens
Route::get('/images/{filename}', [ImageController::class, 'show'])
    ->where('filename', '.*\.(png|jpg|jpeg|gif|webp|svg|bmp)$')
    ->name('image.show');

// Rota para imagens otimizadas (redimensionadas)
Route::get('/images/{width}x{height}/{filename}', [ImageController::class, 'showOptimized'])
    ->where('filename', '.*\.(png|jpg|jpeg|gif|webp|svg|bmp)$')
    ->where('width', '[1-9][0-9]*')
    ->where('height', '[1-9][0-9]*')
    ->name('image.optimized');

// Rota legada para compatibilidade com URLs antigas
Route::get('/storage/produtos/{filename}', function ($filename) {
    return redirect()->route('image.show', ['filename' => $filename], 301);
})->where('filename', '.*\.(png|jpg|jpeg|gif|webp|svg|bmp)$');

// Rota legada para compatibilidade com URLs antigas (otimizadas)
Route::get('/storage/produtos/{width}x{height}/{filename}', function ($width, $height, $filename) {
    return redirect()->route('image.optimized', [
        'width' => $width,
        'height' => $height,
        'filename' => $filename
    ], 301);
})->where('filename', '.*\.(png|jpg|jpeg|gif|webp|svg|bmp)$')
  ->where('width', '[1-9][0-9]*')
  ->where('height', '[1-9][0-9]*');

// ============================================================
// LÓGICA 9: ROTAS API PARA UPLOAD DE IMAGENS (REQUER AUTENTICAÇÃO)
// ============================================================

Route::middleware(['auth'])->prefix('api/images')->name('api.images.')->group(function () {
    Route::post('/upload', [ImageController::class, 'upload'])->name('upload');
    Route::post('/upload-optimized', [ImageController::class, 'uploadOptimized'])->name('upload.optimized');
    Route::delete('/delete', [ImageController::class, 'delete'])->name('delete');
    Route::post('/upload-multiple', [ImageController::class, 'uploadMultiple'])->name('upload.multiple');
});
// routes/web.php
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'db_name' => DB::connection()->getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

// ============================================================
// LÓGICA 10: ROTA PARA TESTAR IMAGENS (APENAS DESENVOLVIMENTO)
// ============================================================

if (app()->environment('local')) {
    Route::get('/test-image/{filename}', function ($filename) {
        $paths = [
            'produtos/' . $filename,
            'uploads/' . $filename,
            'images/' . $filename,
        ];
        
        $results = [];
        foreach ($paths as $path) {
            $results[$path] = [
                'exists' => Storage::disk('public')->exists($path),
                'url' => $path,
                'full_url' => route('image.show', ['filename' => $filename]),
            ];
        }
        
        return response()->json([
            'filename' => $filename,
            'paths' => $results,
            'image_url' => route('image.show', ['filename' => $filename]),
            'optimized_url' => route('image.optimized', [
                'filename' => $filename,
                'width' => 100,
                'height' => 100
            ]),
        ]);
    })->where('filename', '.*\.(png|jpg|jpeg|gif|webp|svg|bmp)$');
}