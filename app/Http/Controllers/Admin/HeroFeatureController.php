<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeroFeatureController extends Controller
{
    public function index(): View
    {
        $features = HeroFeature::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.hero-features.index', compact('features'));
    }

    public function update(Request $request, HeroFeature $heroFeature): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:160'],
            'icon' => ['required', 'string', 'max:100', 'regex:/^bx[srl]? bx-[a-z0-9-]+$/'],
            'color' => ['required', 'in:warning,success,info,danger,primary,secondary'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'icon.regex' => 'Ikon harus berupa class Boxicons, contoh: bx bx-laptop.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $heroFeature->update($validated);

        return redirect()->route('admin.hero-features.index')
            ->with('success', 'Keunggulan hero berhasil diperbarui.');
    }
}
