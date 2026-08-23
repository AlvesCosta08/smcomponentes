<?php
// app/Services/Contracts/ProductServiceInterface.php

namespace App\Services\Contracts;

use App\DTOs\Requests\CreateProductRequestDTO; // Usando o DTO específico de criação
use App\DTOs\Requests\UpdateProductRequestDTO;
use App\DTOs\Responses\ProductResponseDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductServiceInterface
{
    public function listProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getProductById(int $id): ?ProductResponseDTO;
    public function getProductBySlug(string $slug): ?ProductResponseDTO;
    
    public function createProduct(CreateProductRequestDTO $dto): ProductResponseDTO;
    public function updateProduct(int $id, UpdateProductRequestDTO $dto): ProductResponseDTO;
    public function deleteProduct(int $id): bool;
    public function restoreProduct(int $id): bool;
    
    public function getStats(): array;
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;
    public function getByCategory(string $category, int $perPage = 15): LengthAwarePaginator;
    
    public function exportProducts(array $filters = []): string;
}