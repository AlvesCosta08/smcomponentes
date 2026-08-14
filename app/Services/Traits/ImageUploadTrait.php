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
    public function uploadImage(UploadedFile $file, string $path = 'images', ?string $name = null): string
    {
        $filename = $name ?? time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        $file->storeAs($path, $filename, 'public');
        
        return $filename;
    }

    /**
     * Deletar imagem
     */
    public function deleteImage(?string $filename, string $path = 'images'): void
    {
        if ($filename && Storage::disk('public')->exists($path . '/' . $filename)) {
            Storage::disk('public')->delete($path . '/' . $filename);
        }
    }

    /**
     * Obter URL da imagem
     */
    public function getImageUrl(?string $filename, string $path = 'images', string $default = 'placeholder.jpg'): string
    {
        if ($filename && Storage::disk('public')->exists($path . '/' . $filename)) {
            return asset("storage/{$path}/{$filename}");
        }

        return asset("images/{$default}");
    }
}