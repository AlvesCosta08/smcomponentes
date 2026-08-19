<?php

namespace App\Http\Controllers;

use App\Services\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    use ImageUploadTrait;

    /**
     * Servir imagem de produto
     */
    public function show($filename)
    {
        // Caminhos possíveis
        $paths = [
            'produtos/' . $filename,
            'storage/produtos/' . $filename,
        ];
        
        foreach ($paths as $path) {
            // Verificar no storage public
            if (Storage::disk('public')->exists($path)) {
                $mime = Storage::disk('public')->mimeType($path);
                $content = Storage::disk('public')->get($path);
                
                return response($content, 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=31536000',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }
        
        // Se não encontrar, usar placeholder
        $placeholder = storage_path('app/public/produtos/placeholder.png');
        if (file_exists($placeholder)) {
            return response()->file($placeholder, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=31536000',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
        
        // Gerar placeholder dinâmico
        $img = imagecreate(200, 200);
        $bg = imagecolorallocate($img, 240, 240, 240);
        $border = imagecolorallocate($img, 200, 200, 200);
        $textColor = imagecolorallocate($img, 100, 100, 100);
        
        imagefill($img, 0, 0, $bg);
        imagerectangle($img, 0, 0, 199, 199, $border);
        imagestring($img, 5, 50, 80, 'Sem Imagem', $textColor);
        imagestring($img, 3, 45, 105, 'Produto', $textColor);
        
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
     * Servir imagem com otimização
     */
    public function showOptimized($filename, $width = 400, $height = 400)
    {
        $path = 'produtos/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            return $this->show($filename);
        }
        
        // Usar o Trait para otimizar
        $imageInfo = $this->getImageInfo($path);
        
        if (!$imageInfo) {
            return $this->show($filename);
        }
        
        // Servir a imagem otimizada
        $content = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path);
        
        return response($content, 200, [
            'Content-Type' => $mime,
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
            'folder' => 'sometimes|string|in:produtos,banners,categorias',
        ]);
        
        try {
            $folder = $request->input('folder', 'produtos');
            $file = $request->file('image');
            
            // Usar o Trait para upload
            $path = $this->uploadImage($file, $folder);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload otimizado via API
     */
    public function uploadOptimized(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'width' => 'sometimes|integer|min:50|max:2000',
            'height' => 'sometimes|integer|min:50|max:2000',
            'quality' => 'sometimes|integer|min:10|max:100',
        ]);
        
        try {
            $file = $request->file('image');
            $width = $request->input('width', 800);
            $height = $request->input('height', 800);
            $quality = $request->input('quality', 85);
            
            // Usar o Trait para upload otimizado
            $path = $this->uploadOptimizedImage($file, 'produtos', $width, $height, $quality);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'width' => $width,
                'height' => $height,
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
            $deleted = $this->deleteImage($request->input('path'));
            
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Imagem deletada com sucesso' : 'Falha ao deletar imagem',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}