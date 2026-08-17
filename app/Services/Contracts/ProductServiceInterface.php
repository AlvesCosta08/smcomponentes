<?php
// app/Services/Contracts/ProductServiceInterface.php

namespace App\Services\Contracts;

use App\DTOs\ProductDTO;
use App\DTOs\Responses\ProductResponseDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductServiceInterface
{
    /**
     * Listar produtos com filtros
     *
     * @param array $filters ['categoria' => string, 'busca' => string, 'status' => string]
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listProducts(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Obter detalhes de um produto
     *
     * @param int|string $id ID ou slug do produto
     * @return ProductResponseDTO|null
     */
    public function getProductDetails($id): ?ProductResponseDTO;

    /**
     * Criar um novo produto
     *
     * @param ProductDTO $dto
     * @return ProductResponseDTO
     */
    public function createProduct(ProductDTO $dto): ProductResponseDTO;

    /**
     * Atualizar um produto
     *
     * @param int $id
     * @param ProductDTO $dto
     * @return ProductResponseDTO
     */
    public function updateProduct(int $id, ProductDTO $dto): ProductResponseDTO;

    /**
     * Excluir um produto (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool;

    /**
     * Restaurar um produto excluído
     *
     * @param int $id
     * @return bool
     */
    public function restoreProduct(int $id): bool;

    /**
     * Obter produtos por categoria
     *
     * @param string $categoria
     * @param int $limit
     * @return Collection
     */
    public function getProductsByCategory(string $categoria, int $limit = 10): Collection;

    /**
     * Obter produtos em destaque
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedProducts(int $limit = 8): Collection;

    /**
     * Obter produtos em oferta
     *
     * @param int $limit
     * @return Collection
     */
    public function getPromotionalProducts(int $limit = 8): Collection;

    /**
     * Obter produtos recentes
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentProducts(int $limit = 8): Collection;

    /**
     * Buscar produtos por termo
     *
     * @param string $term
     * @param int $limit
     * @return Collection
     */
    public function searchProducts(string $term, int $limit = 10): Collection;

    /**
     * Incrementar visualizações do produto
     *
     * @param int $id
     * @return void
     */
    public function incrementViews(int $id): void;

    /**
     * Importar produtos de arquivo CSV
     *
     * @param string $filePath
     * @return array ['imported' => int, 'failed' => int, 'errors' => array]
     */
    public function importProducts(string $filePath): array;

    /**
     * Exportar produtos para CSV
     *
     * @param array $filters
     * @return string Caminho do arquivo
     */
    public function exportProducts(array $filters = []): string;

    /**
     * Obter produtos relacionados (baseado em categoria)
     *
     * @param int $productId
     * @param int $limit
     * @return Collection
     */
    public function getRelatedProducts(int $productId, int $limit = 4): Collection;
}