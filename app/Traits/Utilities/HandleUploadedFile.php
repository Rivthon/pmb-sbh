<?php

namespace App\Traits\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandleUploadedFile
{
    public function uploadFile(UploadedFile $file, string $folderPrefix): string
    {
        $fileExt = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $encodedFileName = Str::uuid()->toString() . '.' . $fileExt;

        // Simpan file ke disk public
        $path = $file->storeAs($folderPrefix, $encodedFileName, 'public');

        if (!$path) {
            throw new \Exception("Gagal menyimpan file.");
        }

        return $encodedFileName;
    }

    public function syncUploadFile(UploadedFile $file, ?string $oldFileName, string $folderPrefix): string
    {
        // Hapus file lama jika ada dan eksis
        if ($oldFileName && Storage::disk('public')->exists("{$folderPrefix}/{$oldFileName}")) {
            Storage::disk('public')->delete("{$folderPrefix}/{$oldFileName}");
        }

        // Upload file baru
        return $this->uploadFile($file, $folderPrefix);
    }
}
