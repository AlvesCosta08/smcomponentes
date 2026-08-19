<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Gerar um ID único para o checkout
        $checkoutId = $request->session()->get('checkout_id');
        
        if (!$checkoutId) {
            $checkoutId = uniqid('checkout_', true);
            $request->session()->put('checkout_id', $checkoutId);
        }
        
        // Log para debug
        if (!app()->isProduction()) {
            Log::info('🛒 Checkout iniciado', [
                'checkout_id' => $checkoutId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        return $next($request);
    }
}