<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ============================================
// ROTAS PÚBLICAS
// ============================================

Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de produtos (públicas)
Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// ROTAS ADMIN (PROTEGIDAS POR ROLE)
// ============================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Limpar cache
    Route::get('/clear-cache', [HomeController::class, 'clearCache'])->name('clear.cache');
    
    // Dashboard admin (será implementado depois)
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});
