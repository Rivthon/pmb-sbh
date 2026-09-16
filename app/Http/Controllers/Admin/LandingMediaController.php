<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LandingMediaController extends Controller
{
    public function index(): View
    {
        $media = LandingMedia::query()->orderBy('id')->get();

        return view('admin.landing-media.index', compact('media'));
    }

    public function update(Request $request, LandingMedia $landingMedium): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'image.required' => 'Silakan pilih gambar yang akan diunggah.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $disk = Storage::disk('public');
        $oldPath = $landingMedium->path;
        $extension = strtolower($request->file('image')->getClientOriginalExtension());
        $filename = $landingMedium->key . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $extension;

        try {
            if (!$disk->exists('landing-media') && !$disk->makeDirectory('landing-media')) {
                throw new \RuntimeException('Folder landing-media tidak dapat dibuat.');
            }

            $newPath = $disk->putFileAs('landing-media', $request->file('image'), $filename);

            if (!$newPath || !$disk->exists($newPath)) {
                throw new \RuntimeException('File gagal ditulis ke penyimpanan publik.');
            }

            $landingMedium->update(['path' => $newPath]);

            if (str_starts_with($oldPath, 'landing-media/') && $oldPath !== $newPath) {
                $disk->delete($oldPath);
            }
        } catch (Throwable $exception) {
            if (isset($newPath) && $newPath && $disk->exists($newPath)) {
                $disk->delete($newPath);
            }

            Log::error('Gagal mengunggah gambar landing page.', [
                'landing_media_id' => $landingMedium->id,
                'disk_root' => config('filesystems.disks.public.root'),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('admin.landing-media.index')
                ->withErrors(['image' => 'Gambar gagal disimpan. Pastikan folder public/storage dapat ditulis, lalu coba kembali.']);
        }

        return redirect()->route('admin.landing-media.index')
            ->with('success', "{$landingMedium->label} berhasil diperbarui.");
    }
}
