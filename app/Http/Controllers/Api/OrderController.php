<?php

namespace App\Http\Controllers\Api;

use App\Application\Pedidos\DTOs\CreateOrderDTO;
use App\Application\Pedidos\Handlers\CreateOrderHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderHandler $createOrderHandler
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            // 1. Montar o DTO com os dados validados e o ID do usuário autenticado
            $dto = CreateOrderDTO::fromRequest(
                userId: $request->user()->id, 
                validatedData: $request->validated()
            );

            // 2. Executar o Caso de Uso (Handler)
            // O Handler retorna a Entidade de Domínio, não o Model Eloquent
            $order = $this->createOrderHandler->handle($dto);

            // 3. Retornar Resposta Padronizada
            return response()->json([
                'success' => true,
                'message' => 'Pedido criado com sucesso!',
                'data' => [
                    'id' => $order->getId(),
                    'numero_pedido' => $order->getNumeroPedido(), // Certifique-se que o Handler retorna isso ou o Model tem esse getter
                    'total' => $order->getTotal(),
                    'status' => $order->getStatus()->label(),
                ]
            ], 201);

        } catch (\DomainException $e) {
            // Erros de regra de negócio (ex: "Estoque insuficiente para o produto X")
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422); // 422 Unprocessable Entity
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar pedido via API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro interno ao processar seu pedido.',
            ], 500);
        }
    }
}