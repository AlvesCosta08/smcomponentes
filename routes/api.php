<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ============================================
// WEBHOOKS (Públicos - Sem autenticação)
// ============================================

Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago'])
    ->name('webhook.mercadopago');

// ============================================
// ROTAS AUTENTICADAS (API)
// ============================================

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});