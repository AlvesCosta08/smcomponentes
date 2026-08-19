<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    public function handle(Request $request, Closure $next)
    {
        // Se o usuário está autenticado e está na página de login ou registro
        if (Auth::check() && ($request->routeIs('login') || $request->routeIs('register'))) {
            $user = Auth::user();
            
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}