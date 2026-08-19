<?php
// app/Services/Contracts/BannerServiceInterface.php

namespace App\Services\Contracts;

use App\Models\Banner;
use App\DTOs\BannerDTO;
use Illuminate\Database\Eloquent\Collection;

interface BannerServiceInterface
{
    /**
     * Listar todos os banners (admin)
     *
     * @param bool $onlyActive
     * @return Collection
     */
    public function listBanners(bool $onlyActive = false): Collection;

    /**
     * Obter banners ativos para exibição (com cache)
     *
     * @return Collection
     */
    public function getActiveBanners(): Collection;

    /**
     * Criar um novo banner
     *
     * @param BannerDTO $dto
     * @return Banner
     */
    public function createBanner(BannerDTO $dto): Banner;

    /**
     * Atualizar um banner
     *
     * @param int $id
     * @param BannerDTO $dto
     * @return Banner
     */
    public function updateBanner(int $id, BannerDTO $dto): Banner;

    /**
     * Excluir um banner
     *
     * @param int $id
     * @return bool
     */
    public function deleteBanner(int $id): bool;

    /**
     * Alterar status do banner
     *
     * @param int $id
     * @param bool $active
     * @return bool
     */
    public function toggleBannerStatus(int $id, bool $active): bool;

    /**
     * Reordenar banners
     *
     * @param array $order [id => position]
     * @return bool
     */
    public function reorderBanners(array $order): bool;

    /**
     * Obter próximo banner para carousel
     *
     * @param int|null $currentId
     * @return Banner|null
     */
    public function getNextBanner(?int $currentId = null): ?Banner;

    /**
     * Limpar cache de banners
     *
     * @return void
     */
    public function clearBannerCache(): void;

    /**
     * Obter estatísticas de banners
     *
     * @return array ['total' => int, 'ativos' => int, 'inativos' => int]
     */
    public function getBannerStats(): array;

    /**
     * Verificar se banner está disponível (data/hora)
     *
     * @param Banner $banner
     * @return bool
     */
    public function isBannerAvailable(Banner $banner): bool;

    /**
     * Upload de imagem do banner
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $existingPath
     * @return string Caminho do arquivo
     */
    public function uploadBannerImage($file, ?string $existingPath = null): string;
}