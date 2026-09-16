<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

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

        $newPath = $request->file('image')->store('landing-media', 'public');
        $oldPath = $landingMedium->path;

        $landingMedium->update(['path' => $newPath]);

        if (str_starts_with($oldPath, 'landing-media/')) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.landing-media.index')
            ->with('success', "{$landingMedium->label} berhasil diperbarui.");
    }
}
