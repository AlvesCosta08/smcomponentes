<?php

namespace App\Infrastructure\Storage;

use App\Interfaces\Storage\ImageUploaderInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalImageUploader implements ImageUploaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function upload(UploadedFile $file, string $directory = 'produtos'): string
    {
        // Usa ->extension() que verifica o MIME type, sendo mais seguro que getClientOriginalExtension()
        $extensao = $file->extension() ?: 'jpg'; 
        
        // Gera um nome único e seguro (UUID)
        $nomeArquivo = Str::uuid()->toString() . '.' . $extensao;

        // Salva no disco 'public'
        return $file->storeAs($directory, $nomeArquivo, 'public');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): void
    {
        // Verifica se o caminho não está vazio e se o arquivo realmente existe antes de deletar
        if (!empty($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}