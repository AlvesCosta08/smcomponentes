<?php

namespace App\Interfaces\Storage;

use Illuminate\Http\UploadedFile;

interface ImageUploaderInterface
{
    public function upload(UploadedFile $file, string $directory = 'produtos'): string;
    public function delete(string $path): void;
}