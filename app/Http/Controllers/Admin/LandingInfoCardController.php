<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingInfoCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingInfoCardController extends Controller
{
    public function index(): View
    {
        $cards = LandingInfoCard::query()->orderBy('section')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.landing-info-cards.index', [
            'advantageCards' => $cards->where('section', LandingInfoCard::SECTION_ADVANTAGES),
            'costCards' => $cards->where('section', LandingInfoCard::SECTION_COST_INFO),
        ]);
    }

    public function update(Request $request, LandingInfoCard $landingInfoCard): RedirectResponse
    {
        $rules = [
            'title' => ['required', 'string', 'max:120'],
            'icon' => ['required', 'string', 'max:100'],
            'color' => ['required', 'in:warning,success,info,danger,primary,dark,secondary'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($landingInfoCard->section === LandingInfoCard::SECTION_ADVANTAGES) {
            $rules['description'] = ['required', 'string', 'max:500'];
        } else {
            $rules['items_text'] = ['required', 'string', 'max:3000'];
        }

        $validated = $request->validate($rules, [
            'items_text.required' => 'Daftar poin tidak boleh kosong.',
        ]);

        $normalizedIcon = LandingInfoCard::normalizeIcon($validated['icon']);
        if ($normalizedIcon === 'bx bx-image-alt' && trim(strtolower($validated['icon'])) !== 'bx bx-image-alt') {
            return redirect()->route('admin.landing-info-cards.index')
                ->withErrors(['icon' => 'Format ikon tidak dikenali. Gunakan contoh: bx bxs-school atau bxs-school.'])
                ->withInput();
        }

        $data = [
            'title' => $validated['title'],
            'icon' => $normalizedIcon,
            'color' => $validated['color'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($landingInfoCard->section === LandingInfoCard::SECTION_ADVANTAGES) {
            $data['description'] = $validated['description'];
        } else {
            $data['items'] = collect(preg_split('/\r\n|\r|\n/', $validated['items_text']))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->values()
                ->all();
        }

        $landingInfoCard->update($data);

        return redirect()->route('admin.landing-info-cards.index')
            ->with('success', 'Konten landing page berhasil diperbarui.');
    }
}
