<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Alias para middlewares de permissão
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'checkout' => \App\Http\Middleware\CheckoutMiddleware::class,
            'redirect.role' => \App\Http\Middleware\RedirectBasedOnRole::class,
        ]);

        // ✅ Middlewares globais (executados em todas as requisições)
        $middleware->append([
            \App\Http\Middleware\RedirectBasedOnRole::class,
        ]);

        // 🔒 MANTER CSRF ATIVO PARA SEGURANÇA
        // NÃO desabilitar CSRF para login!
        $middleware->validateCsrfTokens(except: [
            // 'carrinho/*', // DESCOMENTE APENAS EM ÚLTIMO CASO
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();