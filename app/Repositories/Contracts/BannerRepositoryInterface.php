<?php
// app/Repositories/Contracts/BannerRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;

interface BannerRepositoryInterface extends RepositoryInterface
{
    /**
     * Buscar banners ativos
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Buscar banners por ordem
     *
     * @return Collection
     */
    public function getOrdered(): Collection;

    /**
     * Buscar banners disponíveis (ativo + data)
     *
     * @return Collection
     */
    public function getAvailable(): Collection;

    /**
     * Buscar banners por posição
     *
     * @param string $position
     * @return Collection
     */
    public function findByPosition(string $position): Collection;

    /**
     * Ativar/desativar banner
     *
     * @param int $id
     * @param bool $active
     * @return Banner
     */
    public function toggleStatus(int $id, bool $active): Banner;

    /**
     * Reordenar banners
     *
     * @param array $order [id => position]
     * @return bool
     */
    public function reorder(array $order): bool;

    /**
     * Obter banners com cache
     *
     * @return Collection
     */
    public function getCached(): Collection;

    /**
     * Limpar cache de banners
     *
     * @return void
     */
    public function clearCache(): void;

    /**
     * Verificar se banner está disponível
     *
     * @param Banner $banner
     * @return bool
     */
    public function isAvailable(Banner $banner): bool;

    /**
     * Obter estatísticas
     *
     * @return array
     */
    public function getStats(): array;
}