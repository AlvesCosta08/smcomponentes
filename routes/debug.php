<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Banner;

// ============================================================
// ROTAS DE DEBUG (APENAS DESENVOLVIMENTO)
// ============================================================

Route::prefix('debug')->name('debug.')->group(function () {
    
    // --- 1. Debug de Banners ---
    Route::get('/banners', function() {
        $banners = Banner::ativo()->ordenado()->get();
        
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
                'existe_no_storage' => Storage::disk('public')->exists($banner->imagem),
                'caminho_completo' => public_path('storage/' . $banner->imagem),
                'arquivo_existe' => file_exists(public_path('storage/' . $banner->imagem)),
            ];
        }
        
        return response()->json([
            'total' => $banners->count(),
            'banners' => $result
        ]);
    })->name('banners.list');  // ← ALTERADO: de 'banners' para 'banners.list'

    // --- 2. Debug de Cache ---
    Route::get('/cache', function() {
        $cachedBanners = Cache::get('banners_ativos');
        
        return response()->json([
            'cache_exists' => Cache::has('banners_ativos'),
            'cache_data' => $cachedBanners,
            'cache_keys' => $cachedBanners ? array_keys((array)$cachedBanners) : []
        ]);
    })->name('cache');

    // --- 3. Debug de Storage ---
    Route::get('/storage', function() {
        $files = Storage::disk('public')->files('banners');
        
        return response()->json([
            'storage_path' => storage_path('app/public'),
            'public_path' => public_path('storage'),
            'files_in_storage' => $files,
            'link_exists' => is_link(public_path('storage')),
            'link_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
        ]);
    })->name('storage');
    
    // --- 4. Debug de Rotas ---
    Route::get('/routes', function() {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            $routes[] = [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        }
        return response()->json($routes);
    })->name('routes');
    
    // --- 5. Debug de Configurações ---
    Route::get('/config', function() {
        return response()->json([
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
            ],
        ]);
    })->name('config');
    
    // --- 6. Debug de E-mail ---
    Route::get('/mail', function() {
        try {
            return response()->json([
                'status' => 'success',
                'config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                ],
                'from' => config('mail.from'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('mail');
});