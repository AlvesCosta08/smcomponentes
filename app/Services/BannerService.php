<?php
// app/Services/BannerService.php

namespace App\Services;

use App\Models\Banner;
use App\Services\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BannerService
{
    use ImageUploadTrait;

    /**
     * Listar todos os banners
     */
    public function listBanners(): \Illuminate\Database\Eloquent\Collection
    {
        return Banner::orderBy('ordem')->get();
    }

    /**
     * Listar banners ativos
     */
    public function listActiveBanners(): \Illuminate\Database\Eloquent\Collection
    {
        return Banner::ativo()
            ->orderBy('ordem')
            ->get();
    }

    /**
     * Criar novo banner
     */
    public function createBanner(array $data): Banner
    {
        try {
            DB::beginTransaction();

            // Upload da imagem
            if (isset($data['imagem_file'])) {
                $data['imagem'] = $this->uploadImage($data['imagem_file'], 'banners');
                unset($data['imagem_file']);
            }

            // Definir ordem automaticamente
            if (empty($data['ordem'])) {
                $data['ordem'] = Banner::max('ordem') + 1;
            }

            $banner = Banner::create($data);

            DB::commit();

            Log::info('Banner criado com sucesso', [
                'banner_id' => $banner->id,
                'usuario_id' => auth()->id()
            ]);

            return $banner;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar banner: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualizar banner
     */
    public function updateBanner(Banner $banner, array $data): Banner
    {
        try {
            DB::beginTransaction();

            // Upload da nova imagem
            if (isset($data['imagem_file'])) {
                if ($banner->imagem) {
                    $this->deleteImage($banner->imagem, 'banners');
                }
                $data['imagem'] = $this->uploadImage($data['imagem_file'], 'banners');
                unset($data['imagem_file']);
            }

            $banner->update($data);

            DB::commit();

            Log::info('Banner atualizado com sucesso', [
                'banner_id' => $banner->id,
                'usuario_id' => auth()->id()
            ]);

            return $banner->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar banner: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Deletar banner
     */
    public function deleteBanner(Banner $banner): bool
    {
        try {
            DB::beginTransaction();

            // Remover imagem
            if ($banner->imagem) {
                $this->deleteImage($banner->imagem, 'banners');
            }

            $deleted = $banner->delete();

            DB::commit();

            Log::info('Banner deletado com sucesso', [
                'banner_id' => $banner->id,
                'usuario_id' => auth()->id()
            ]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao deletar banner: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reordenar banners
     */
    public function reorderBanners(array $banners): bool
    {
        try {
            DB::beginTransaction();

            foreach ($banners as $index => $id) {
                Banner::where('id', $id)->update(['ordem' => $index + 1]);
            }

            DB::commit();

            Log::info('Banners reordenados com sucesso', [
                'quantidade' => count($banners),
                'usuario_id' => auth()->id()
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reordenar banners: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Alternar status do banner
     */
    public function toggleStatus(Banner $banner): bool
    {
        $banner->ativo = !$banner->ativo;
        return $banner->save();
    }
}