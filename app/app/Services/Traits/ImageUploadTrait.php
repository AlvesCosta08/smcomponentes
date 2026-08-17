<?php
// app/Services/Traits/ImageUploadTrait.php

namespace App\Services\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ImageUploadTrait
{
    /**
     * Upload de imagem
     */
    public function uploadImage(UploadedFile $file, string $folder = 'banners', ?string $name = null): string
    {
        // Gera um nome único para o arquivo
        $filename = $name ?? time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Salva o arquivo na pasta public
        $path = $file->storeAs($folder, $filename, 'public');
        
        // 🔥 RETORNA O CAMINHO COMPLETO (ex: "banners/123456_abc.jpg")
        return $path;
    }

    /**
     * Deletar imagem
     */
    public function deleteImage(?string $path, string $folder = 'banners'): bool
    {
        // Se não tiver caminho, retorna false
        if (!$path) {
            return false;
        }

        // Verifica se o arquivo existe e deleta
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Obter URL da imagem (opcional - já tem no Model)
     */
    public function getImageUrl(?string $path, string $default = null): ?string
    {
        if (!$path) {
            return $default;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return $default;
    }
}