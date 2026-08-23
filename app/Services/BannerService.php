<?php

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
        Log::info('📋 Listando banners');
        return Banner::orderBy('ordem')->get();
    }

    /**
     * Listar banners ativos
     */
    public function listActiveBanners(): \Illuminate\Database\Eloquent\Collection
    {
        Log::info('📋 Listando banners ativos');
        return Banner::ativo()->ordenado()->get();
    }

    /**
     * Criar novo banner
     */
    public function createBanner(array $data): Banner
    {
        Log::info('🚀 INICIANDO CRIAÇÃO DE BANNER');

        try {
            DB::beginTransaction();

            // Upload da imagem
            if (isset($data['imagem_file'])) {
                $uploadResult = $this->uploadImage($data['imagem_file'], 'banners');
                $data['imagem'] = 'banners/' . basename($uploadResult);
                Log::info('✅ Upload realizado', ['caminho' => $data['imagem']]);
                unset($data['imagem_file']);
            }

            // Definir ordem automaticamente
            if (empty($data['ordem'])) {
                $maxOrdem = Banner::max('ordem') ?? 0;
                $data['ordem'] = $maxOrdem + 1;
            }

            $banner = Banner::create($data);

            DB::commit();
            Log::info('🎉 Banner criado com sucesso', ['id' => $banner->id]);

            return $banner;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO CRIAR BANNER', ['mensagem' => $e->getMessage()]);
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
                // Remover imagem antiga
                if ($banner->imagem) {
                    $this->deleteImage($banner->imagem);
                }
                
                $uploadResult = $this->uploadImage($data['imagem_file'], 'banners');
                $data['imagem'] = 'banners/' . basename($uploadResult);
                unset($data['imagem_file']);
            }

            $banner->update($data);

            DB::commit();
            Log::info('🎉 Banner atualizado com sucesso', ['id' => $banner->id]);

            return $banner->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO ATUALIZAR BANNER', ['mensagem' => $e->getMessage()]);
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
                $this->deleteImage($banner->imagem);
            }

            $deleted = $banner->delete();

            DB::commit();
            Log::info('🎉 Banner deletado com sucesso', ['id' => $banner->id]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO DELETAR BANNER', ['mensagem' => $e->getMessage()]);
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
            Log::info('🎉 Banners reordenados com sucesso');

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO REORDENAR BANNERS', ['mensagem' => $e->getMessage()]);
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