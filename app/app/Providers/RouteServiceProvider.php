<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // 🔥 CONFIGURAÇÃO DE RATE LIMITING (OPCIONAL)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // 🔥 CARREGA ROTAS DA API SE O ARQUIVO EXISTIR
            $apiPath = base_path('routes/api.php');
            if (file_exists($apiPath)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($apiPath);
            }

            // 🔥 CARREGA ROTAS DE BROADCAST SE O ARQUIVO EXISTIR
            $channelsPath = base_path('routes/channels.php');
            if (file_exists($channelsPath)) {
                Route::middleware('web')
                    ->group($channelsPath);
            }

            // 🔥 CARREGA ROTAS WEB (SEMPRE)
            $webPath = base_path('routes/web.php');
            if (file_exists($webPath)) {
                Route::middleware('web')
                    ->group($webPath);
            }
        });
    }
}