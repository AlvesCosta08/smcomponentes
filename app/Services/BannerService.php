<?php
// app/Services/BannerService.php

namespace App\Services;

use App\Models\Banner;
use App\Services\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        return Banner::ativo()
            ->orderBy('ordem')
            ->get();
    }

    /**
     * Criar novo banner
     */
    public function createBanner(array $data): Banner
    {
        Log::info('🚀 INICIANDO CRIAÇÃO DE BANNER', ['dados_recebidos' => array_keys($data)]);

        try {
            DB::beginTransaction();
            Log::info('🔄 Transação iniciada');

            // Upload da imagem
            if (isset($data['imagem_file'])) {
                Log::info('📁 Processando upload da imagem', [
                    'tipo' => get_class($data['imagem_file']),
                    'nome_original' => method_exists($data['imagem_file'], 'getClientOriginalName') ? 
                        $data['imagem_file']->getClientOriginalName() : 'N/A',
                    'tamanho' => method_exists($data['imagem_file'], 'getSize') ? 
                        $data['imagem_file']->getSize() : 'N/A'
                ]);

                try {
                    $uploadResult = $this->uploadImage($data['imagem_file'], 'banners');
                    Log::info('✅ Upload realizado com sucesso', [
                        'caminho_salvo' => $uploadResult,
                        'pasta' => 'banners'
                    ]);
                    $data['imagem'] = $uploadResult;
                } catch (\Exception $e) {
                    Log::error('❌ Falha no upload da imagem', [
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                unset($data['imagem_file']);
            } else {
                Log::warning('⚠️ Nenhum arquivo de imagem enviado');
            }

            // Definir ordem automaticamente
            if (empty($data['ordem'])) {
                $maxOrdem = Banner::max('ordem') ?? 0;
                $data['ordem'] = $maxOrdem + 1;
                Log::info('📊 Ordem definida automaticamente', ['ordem' => $data['ordem']]);
            }

            Log::info('📝 Criando registro no banco', ['dados' => $data]);
            $banner = Banner::create($data);

            DB::commit();
            Log::info('✅ Transação finalizada com sucesso');

            Log::info('🎉 Banner criado com sucesso', [
                'banner_id' => $banner->id,
                'titulo' => $banner->titulo ?? 'Sem título',
                'imagem' => $banner->imagem ?? 'Sem imagem',
                'usuario_id' => auth()->id()
            ]);

            return $banner;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO CRIAR BANNER', [
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar banner
     */
    public function updateBanner(Banner $banner, array $data): Banner
    {
        Log::info('🔄 INICIANDO ATUALIZAÇÃO DE BANNER', [
            'banner_id' => $banner->id,
            'titulo_atual' => $banner->titulo,
            'dados_recebidos' => array_keys($data)
        ]);

        try {
            DB::beginTransaction();
            Log::info('🔄 Transação iniciada');

            // Upload da nova imagem
            if (isset($data['imagem_file'])) {
                Log::info('📁 Nova imagem recebida para atualização', [
                    'nome_original' => method_exists($data['imagem_file'], 'getClientOriginalName') ? 
                        $data['imagem_file']->getClientOriginalName() : 'N/A',
                    'tamanho' => method_exists($data['imagem_file'], 'getSize') ? 
                        $data['imagem_file']->getSize() : 'N/A'
                ]);

                // Remover imagem antiga
                if ($banner->imagem) {
                    Log::info('🗑️ Removendo imagem antiga', ['imagem' => $banner->imagem]);
                    $currentImage = $this->sanitizeImagePath($banner->imagem);
                    if ($currentImage) {
                        $deleted = $this->deleteImage($currentImage, 'banners');
                        Log::info('Resultado da exclusão da imagem antiga', ['sucesso' => $deleted]);
                    }
                }
                
                $data['imagem'] = $this->uploadImage($data['imagem_file'], 'banners');
                Log::info('✅ Nova imagem salva', ['caminho' => $data['imagem']]);
                unset($data['imagem_file']);
            }

            Log::info('📝 Atualizando registro no banco', ['dados' => $data]);
            $banner->update($data);

            DB::commit();
            Log::info('✅ Transação finalizada com sucesso');

            Log::info('🎉 Banner atualizado com sucesso', [
                'banner_id' => $banner->id,
                'titulo' => $banner->titulo ?? 'Sem título',
                'imagem' => $banner->imagem ?? 'Sem imagem',
                'usuario_id' => auth()->id()
            ]);

            return $banner->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO ATUALIZAR BANNER', [
                'banner_id' => $banner->id,
                'mensagem' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Deletar banner
     */
    public function deleteBanner(Banner $banner): bool
    {
        Log::info('🗑️ INICIANDO EXCLUSÃO DE BANNER', [
            'banner_id' => $banner->id,
            'titulo' => $banner->titulo ?? 'Sem título'
        ]);

        try {
            DB::beginTransaction();
            Log::info('🔄 Transação iniciada');

            // Remover imagem
            if ($banner->imagem) {
                Log::info('📁 Removendo imagem do banner', ['imagem' => $banner->imagem]);
                $currentImage = $this->sanitizeImagePath($banner->imagem);
                if ($currentImage) {
                    $deleted = $this->deleteImage($currentImage, 'banners');
                    Log::info('Resultado da exclusão da imagem', ['sucesso' => $deleted]);
                }
            }

            $deleted = $banner->delete();
            Log::info('📝 Registro deletado do banco', ['sucesso' => $deleted]);

            DB::commit();
            Log::info('✅ Transação finalizada com sucesso');

            Log::info('🎉 Banner deletado com sucesso', [
                'banner_id' => $banner->id,
                'usuario_id' => auth()->id()
            ]);

            return $deleted;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO DELETAR BANNER', [
                'banner_id' => $banner->id,
                'mensagem' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reordenar banners
     */
    public function reorderBanners(array $banners): bool
    {
        Log::info('🔄 INICIANDO REORDENAÇÃO DE BANNERS', [
            'quantidade' => count($banners),
            'ids' => $banners
        ]);

        try {
            DB::beginTransaction();
            Log::info('🔄 Transação iniciada');

            foreach ($banners as $index => $id) {
                Banner::where('id', $id)->update(['ordem' => $index + 1]);
                Log::info("📊 Banner ID {$id} recebeu ordem " . ($index + 1));
            }

            DB::commit();
            Log::info('✅ Transação finalizada com sucesso');

            Log::info('🎉 Banners reordenados com sucesso', [
                'quantidade' => count($banners),
                'usuario_id' => auth()->id()
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERRO AO REORDENAR BANNERS', [
                'erro' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Alternar status do banner
     */
    public function toggleStatus(Banner $banner): bool
    {
        Log::info('🔄 ALTERANDO STATUS DO BANNER', [
            'banner_id' => $banner->id,
            'status_atual' => $banner->ativo ? 'Ativo' : 'Inativo'
        ]);

        $banner->ativo = !$banner->ativo;
        $result = $banner->save();

        Log::info('📊 Status alterado', [
            'banner_id' => $banner->id,
            'novo_status' => $banner->ativo ? 'Ativo' : 'Inativo',
            'sucesso' => $result
        ]);

        return $result;
    }

    // ============================================================
    // 🔧 FUNÇÃO AUXILIAR
    // ============================================================
    /**
     * Remove a URL completa e deixa apenas o caminho relativo.
     * Ex: 'http://localhost:8000/storage/banners/nome.jpg' vira 'banners/nome.jpg'
     */
    private function sanitizeImagePath($path)
    {
        if (empty($path)) {
            Log::debug('📂 sanitizeImagePath: caminho vazio');
            return null;
        }

        Log::debug('📂 sanitizeImagePath: processando', ['path_original' => $path]);

        // Remove tudo antes de 'banners/'
        if (str_contains($path, 'banners/')) {
            $parts = explode('banners/', $path);
            $result = 'banners/' . end($parts);
            Log::debug('📂 sanitizeImagePath: resultado', ['path_sanitizado' => $result]);
            return $result;
        }

        Log::debug('📂 sanitizeImagePath: mantendo path original', ['path_final' => $path]);
        return $path;
    }
}