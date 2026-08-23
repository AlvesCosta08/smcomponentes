<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Servir imagem de produto
     */
    public function show($filename)
    {
        // Remove caracteres especiais do nome do arquivo
        $filename = basename($filename);
        
        // Mapeamento de pastas por tipo de imagem
        $folders = [
            'produtos/',
            'banners/',
            'categorias/',
            'uploads/',
            'images/',
            'produtos/images/',
        ];
        
        // Verifica em todas as pastas
        foreach ($folders as $folder) {
            $path = $folder . $filename;
            
            // Verificar no storage public
            if (Storage::disk('public')->exists($path)) {
                $mime = Storage::disk('public')->mimeType($path);
                $content = Storage::disk('public')->get($path);
                
                // Cache por 1 ano
                return response($content, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                    'Access-Control-Allow-Origin' => '*',
                    'Content-Length' => strlen($content),
                ]);
            }
        }
        
        // Verifica em storage/produtos (legado - sem o prefixo public)
        if (Storage::exists('produtos/' . $filename)) {
            $content = Storage::get('produtos/' . $filename);
            $mime = Storage::mimeType('produtos/' . $filename);
            
            return response($content, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
        
        // Verifica se existe diretamente na raiz do storage
        if (Storage::exists($filename)) {
            $content = Storage::get($filename);
            $mime = Storage::mimeType($filename);
            
            return response($content, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
        
        // Se não encontrar, usar placeholder
        return $this->getPlaceholder();
    }

    /**
     * Servir imagem com otimização
     */
    public function showOptimized($filename, $width = 400, $height = 400)
    {
        $filename = basename($filename);
        
        // Limita o tamanho máximo
        $width = min(max((int)$width, 50), 2000);
        $height = min(max((int)$height, 50), 2000);
        
        // Busca a imagem
        $paths = [
            'produtos/' . $filename,
            'uploads/' . $filename,
            'images/' . $filename,
            'produtos/images/' . $filename,
        ];
        
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                // Verifica se já existe versão otimizada em cache
                $cachePath = 'cache/' . $width . 'x' . $height . '/' . $filename;
                
                if (Storage::disk('public')->exists($cachePath)) {
                    $content = Storage::disk('public')->get($cachePath);
                    $mime = Storage::disk('public')->mimeType($cachePath);
                } else {
                    // Otimiza a imagem
                    $content = $this->optimizeImage($path, $width, $height);
                    
                    // Salva em cache
                    Storage::disk('public')->put($cachePath, $content);
                    $mime = Storage::disk('public')->mimeType($path);
                }
                
                return response($content, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }
        
        // Se não encontrou, retorna placeholder redimensionado
        return $this->getPlaceholder();
    }

    /**
     * Otimiza uma imagem (redimensiona e comprime)
     */
    private function optimizeImage($path, $width, $height)
    {
        $content = Storage::disk('public')->get($path);
        
        // Tenta usar Intervention Image se disponível
        if (class_exists(\Intervention\Image\Facades\Image::class)) {
            try {
                $img = \Intervention\Image\Facades\Image::make($content);
                $img->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
                $img->encode('jpg', 80);
                return (string) $img;
            } catch (\Exception $e) {
                // Fallback para GD se falhar
            }
        }
        
        // Fallback: usar GD
        try {
            $info = getimagesizefromstring($content);
            if (!$info) {
                return $content;
            }
            
            $mime = $info['mime'] ?? 'image/jpeg';
            
            // Cria imagem a partir do conteúdo
            switch ($mime) {
                case 'image/jpeg':
                    $src = imagecreatefromjpeg($content);
                    break;
                case 'image/png':
                    $src = imagecreatefrompng($content);
                    break;
                case 'image/gif':
                    $src = imagecreatefromgif($content);
                    break;
                case 'image/webp':
                    $src = imagecreatefromwebp($content);
                    break;
                default:
                    return $content;
            }
            
            if (!$src) {
                return $content;
            }
            
            // Cria imagem redimensionada
            $dst = imagecreatetruecolor($width, $height);
            
            // Mantém transparência para PNG
            if ($mime === 'image/png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
            }
            
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
            
            // Output
            ob_start();
            if ($mime === 'image/png') {
                imagepng($dst, null, 8);
            } elseif ($mime === 'image/webp') {
                imagewebp($dst, null, 80);
            } else {
                imagejpeg($dst, null, 80);
            }
            $output = ob_get_clean();
            
            imagedestroy($src);
            imagedestroy($dst);
            
            return $output;
            
        } catch (\Exception $e) {
            return $content;
        }
    }

    /**
     * Gera placeholder dinâmico
     */
    private function getPlaceholder()
    {
        // Tenta carregar placeholder do storage
        $placeholderPaths = [
            storage_path('app/public/produtos/placeholder.png'),
            storage_path('app/public/images/placeholder.png'),
            storage_path('app/public/placeholder.png'),
        ];
        
        foreach ($placeholderPaths as $placeholder) {
            if (file_exists($placeholder)) {
                return response()->file($placeholder, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=31536000',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }
        
        // Tenta carregar placeholder da pasta public
        $placeholderPublic = public_path('images/produto-placeholder.jpg');
        if (file_exists($placeholderPublic)) {
            return response()->file($placeholderPublic, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
        
        // Gera placeholder dinâmico com texto
        $width = 200;
        $height = 200;
        $img = imagecreate($width, $height);
        
        // Cores
        $bg = imagecolorallocate($img, 240, 240, 240);
        $border = imagecolorallocate($img, 200, 200, 200);
        $textColor = imagecolorallocate($img, 100, 100, 100);
        $iconColor = imagecolorallocate($img, 180, 180, 180);
        
        // Fundo
        imagefill($img, 0, 0, $bg);
        imagerectangle($img, 0, 0, $width-1, $height-1, $border);
        
        // Ícone (desenho simples de uma caixa)
        imagefilledrectangle($img, 65, 65, 135, 135, $iconColor);
        imagerectangle($img, 60, 60, 140, 140, $border);
        
        // Texto
        $text = 'Sem Imagem';
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textX = ($width - $textWidth) / 2;
        imagestring($img, $fontSize, $textX, 155, $text, $textColor);
        
        // Output
        ob_start();
        imagepng($img);
        $imageData = ob_get_clean();
        imagedestroy($img);
        
        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Upload de imagem via API
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'folder' => 'sometimes|string|in:produtos,banners,categorias,uploads',
        ]);
        
        try {
            $folder = $request->input('folder', 'produtos');
            $file = $request->file('image');
            
            // Gera nome único
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $folder . '/' . $filename;
            
            // Salva no storage
            Storage::disk('public')->put($path, file_get_contents($file));
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'url' => route('image.show', ['filename' => $filename]),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deletar imagem via API
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);
        
        try {
            $path = $request->input('path');
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                // Deleta também o cache se existir
                $cacheDir = 'cache/';
                $files = Storage::disk('public')->files($cacheDir);
                foreach ($files as $file) {
                    if (strpos($file, basename($path)) !== false) {
                        Storage::disk('public')->delete($file);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Imagem deletada com sucesso',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Imagem não encontrada',
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}