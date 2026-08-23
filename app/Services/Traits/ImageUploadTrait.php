<?php
// app/Services/Traits/ImageUploadTrait.php

namespace App\Services\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait ImageUploadTrait
{
    /**
     * Upload de imagem para produtos (compatível com Laravel 13)
     * 
     * @param UploadedFile $file
     * @param string $folder (produtos, banners, categorias, etc)
     * @param string|null $name
     * @return string caminho completo (ex: "produtos/123_abc.jpg")
     */
    public function uploadImage(UploadedFile $file, string $folder = 'produtos', ?string $name = null): string
    {
        // Validar a imagem antes do upload
        $this->validateImage($file);
        
        // Gera nome único
        $filename = $name ?? time() . '_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
        
        // Salva no disco public (storage/app/public/pasta)
        $path = $file->storeAs($folder, $filename, 'public');
        
        // Log para debug
        Log::info('Imagem salva', [
            'path' => $path,
            'folder' => $folder,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);
        
        return $path;
    }

    /**
     * Upload de imagem com redimensionamento (usando Intervention Image)
     * Compatível com Laravel 13
     */
    public function uploadOptimizedImage(
        UploadedFile $file, 
        string $folder = 'produtos', 
        int $width = 800, 
        int $height = 800,
        int $quality = 85
    ): string {
        // Validar a imagem
        $this->validateImage($file);
        
        // Gera nome único
        $filename = time() . '_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        try {
            // Verifica se Intervention Image está instalado (Laravel 13)
            if (class_exists('Intervention\Image\ImageManager')) {
                $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
                $image = $manager->make($file->getRealPath());
                
                // Redimensionar mantendo proporção
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                // Otimizar qualidade
                $image->encode(null, $quality);
                
                // Salvar no storage
                Storage::disk('public')->put($path, (string) $image->encode());
                
                Log::info('Imagem otimizada salva', [
                    'path' => $path,
                    'width' => $width,
                    'height' => $height,
                    'quality' => $quality
                ]);
            } else {
                // Fallback: salvar sem redimensionar
                Log::warning('Intervention Image não encontrado, salvando imagem sem otimização');
                Storage::disk('public')->putFileAs($folder, $file, $filename);
            }
            
            return $path;

        } catch (\Exception $e) {
            Log::error('Erro ao otimizar imagem', [
                'error' => $e->getMessage(),
                'path' => $path,
                'file' => $file->getClientOriginalName()
            ]);
            
            // Fallback: salvar sem otimização
            Storage::disk('public')->putFileAs($folder, $file, $filename);
            return $path;
        }
    }

    /**
     * Upload de múltiplas imagens (galeria)
     */
    public function uploadMultipleImages(array $files, string $folder = 'produtos/galeria'): array
    {
        $paths = [];
        $errors = [];
        
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $paths[] = $this->uploadImage($file, $folder);
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $key,
                        'error' => $e->getMessage(),
                        'file' => $file->getClientOriginalName()
                    ];
                    Log::error('Erro no upload de imagem múltipla', [
                        'error' => $e->getMessage(),
                        'file' => $file->getClientOriginalName()
                    ]);
                }
            }
        }
        
        if (!empty($errors)) {
            Log::warning('Algumas imagens da galeria falharam no upload', [
                'errors' => $errors,
                'total' => count($files),
                'success' => count($paths)
            ]);
        }
        
        return $paths;
    }

    /**
     * Atualizar imagem (remove antiga e salva nova)
     */
    public function updateImage(
        ?UploadedFile $newFile,
        ?string $oldPath,
        string $folder = 'produtos',
        bool $optimize = true
    ): ?string {
        if (!$newFile) {
            return $oldPath;
        }

        // Remove imagem antiga
        if ($oldPath) {
            $this->deleteImage($oldPath);
        }

        // Faz upload da nova (otimizada ou normal)
        if ($optimize) {
            return $this->uploadOptimizedImage($newFile, $folder);
        }
        
        return $this->uploadImage($newFile, $folder);
    }

    /**
     * Deletar imagem com verificação
     */
    public function deleteImage(?string $path): bool
    {
        if (!$path) {
            Log::debug('Tentativa de deletar imagem com path vazio');
            return false;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                $deleted = Storage::disk('public')->delete($path);
                
                if ($deleted) {
                    Log::info('Imagem deletada com sucesso', ['path' => $path]);
                } else {
                    Log::warning('Falha ao deletar imagem', ['path' => $path]);
                }
                
                return $deleted;
            } else {
                Log::warning('Imagem não encontrada para deletar', ['path' => $path]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao deletar imagem', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Deletar múltiplas imagens
     */
    public function deleteMultipleImages(array $paths): void
    {
        $deleted = 0;
        $failed = 0;
        
        foreach ($paths as $path) {
            if ($this->deleteImage($path)) {
                $deleted++;
            } else {
                $failed++;
            }
        }
        
        Log::info('Deleção múltipla de imagens concluída', [
            'total' => count($paths),
            'deleted' => $deleted,
            'failed' => $failed
        ]);
    }

    /**
     * Obter URL pública da imagem
     */
    public function getImageUrl(?string $path, ?string $default = null): ?string
    {
        if (!$path) {
            return $default;
        }

        // Se já for uma URL completa, retorna
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Verifica se existe no storage public
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Tenta verificar em pastas alternativas
        $filename = basename($path);
        $folders = ['banners', 'produtos', 'images'];
        
        foreach ($folders as $folder) {
            $testPath = $folder . '/' . $filename;
            if (Storage::disk('public')->exists($testPath)) {
                return Storage::disk('public')->url($testPath);
            }
        }

        Log::debug('Imagem não encontrada no storage', ['path' => $path]);
        return $default;
    }

    /**
     * Verificar se a imagem existe
     */
    public function imageExists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            try {
                $headers = @get_headers($path);
                return $headers && strpos($headers[0], '200') !== false;
            } catch (\Exception $e) {
                return false;
            }
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Validar imagem com regras melhoradas
     */
    public function validateImage(UploadedFile $file, int $maxSize = 2048, array $allowedTypes = null): bool
    {
        $allowedTypes = $allowedTypes ?? ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'];
        
        if ($file->getSize() > $maxSize * 1024) {
            throw new \Exception("Arquivo muito grande. Máximo: {$maxSize}KB. Atual: " . round($file->getSize() / 1024, 2) . "KB");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception("Formato não permitido. Permitidos: " . implode(', ', $allowedTypes));
        }
        
        $allowedMimes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 
            'image/gif', 'image/svg+xml', 'image/bmp'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception("Tipo MIME não permitido: {$mimeType}");
        }

        return true;
    }

    /**
     * Obter informações da imagem
     */
    public function getImageInfo(?string $path): ?array
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        try {
            $fullPath = Storage::disk('public')->path($path);
            
            if (!file_exists($fullPath)) {
                return null;
            }
            
            $info = getimagesize($fullPath);
            
            return [
                'width' => $info[0] ?? null,
                'height' => $info[1] ?? null,
                'mime' => $info['mime'] ?? null,
                'size' => Storage::disk('public')->size($path),
                'size_human' => $this->formatBytes(Storage::disk('public')->size($path)),
                'url' => Storage::disk('public')->url($path),
                'path' => $path,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'filename' => pathinfo($path, PATHINFO_FILENAME),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter informações da imagem', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verificar se o storage está acessível
     */
    public function checkStorageAccess(): bool
    {
        try {
            $testPath = 'temp/test_' . time() . '.txt';
            $testContent = 'Teste de acesso ao storage - ' . date('Y-m-d H:i:s');
            
            Storage::disk('public')->put($testPath, $testContent);
            $content = Storage::disk('public')->get($testPath);
            $exists = Storage::disk('public')->exists($testPath);
            Storage::disk('public')->delete($testPath);
            
            Log::info('Storage acessível', ['test_path' => $testPath, 'success' => true]);
            return true;
        } catch (\Exception $e) {
            Log::error('Storage inacessível', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Formatar bytes para legibilidade humana
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Gerar um nome de arquivo único
     */
    private function generateUniqueFilename(UploadedFile $file, string $prefix = ''): string
    {
        $timestamp = time();
        $random = Str::random(12);
        $extension = $file->getClientOriginalExtension();
        $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        
        if ($prefix) {
            return $prefix . '_' . $timestamp . '_' . $originalName . '_' . $random . '.' . $extension;
        }
        
        return $timestamp . '_' . $originalName . '_' . $random . '.' . $extension;
    }

    /**
     * Criar múltiplos tamanhos de uma imagem (thumbnail, medium, large)
     */
    public function createImageVariants(
        UploadedFile $file, 
        string $folder = 'produtos',
        array $sizes = []
    ): array {
        $sizes = $sizes ?: [
            'thumbnail' => ['width' => 150, 'height' => 150, 'quality' => 70],
            'medium' => ['width' => 400, 'height' => 400, 'quality' => 80],
            'large' => ['width' => 800, 'height' => 800, 'quality' => 85],
        ];
        
        $variants = [];
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        foreach ($sizes as $key => $config) {
            $filename = $originalName . '_' . $key . '_' . time() . '.' . $extension;
            $path = $folder . '/' . $filename;
            
            try {
                if (class_exists('Intervention\Image\ImageManager')) {
                    $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
                    $image = $manager->make($file->getRealPath());
                    $image->resize($config['width'], $config['height'], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->encode(null, $config['quality'] ?? 80);
                    
                    Storage::disk('public')->put($path, (string) $image->encode());
                    
                    $variants[$key] = [
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'width' => $config['width'],
                        'height' => $config['height'],
                    ];
                } else {
                    Storage::disk('public')->putFileAs($folder, $file, $filename);
                    $variants[$key] = [
                        'path' => $path,
                        'url' => Storage::disk('public')->url($path),
                        'width' => null,
                        'height' => null,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Erro ao criar variante de imagem', [
                    'variant' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $variants;
    }
}