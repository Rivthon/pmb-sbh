<?php

namespace App\Traits\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandleUploadedFile
{
    public function uploadFile(UploadedFile $file, string $folderPrefix): string
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('File upload tidak valid (kode: ' . $file->getError() . ').');
        }

        $fileExt = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $encodedFileName = Str::uuid()->toString() . '.' . $fileExt;
        $disk = Storage::disk('public');

        if (!$disk->exists($folderPrefix) && !$disk->makeDirectory($folderPrefix)) {
            throw new \RuntimeException("Folder penyimpanan {$folderPrefix} tidak dapat dibuat.");
        }

        // Simpan file ke disk public
        $path = $disk->putFileAs($folderPrefix, $file, $encodedFileName);

        if (!$path || !$disk->exists($path)) {
            throw new \RuntimeException("File gagal disimpan ke {$folderPrefix}.");
        }

        return $encodedFileName;
    }

    public function syncUploadFile(UploadedFile $file, ?string $oldFileName, string $folderPrefix): string
    {
        // Simpan file baru terlebih dahulu agar file lama tidak hilang jika upload gagal.
        $newFileName = $this->uploadFile($file, $folderPrefix);
        $disk = Storage::disk('public');

        if ($oldFileName && $oldFileName !== $newFileName && $disk->exists("{$folderPrefix}/{$oldFileName}")) {
            $disk->delete("{$folderPrefix}/{$oldFileName}");
        }

        return $newFileName;
    }
}
