<?php

namespace App\Http\Controllers;
use DOMXPath;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class BeritaController extends Controller
{
    /**
     * Display the berita index page.
     */
    public function index(): View
    {
        return view('berita.index');
    }

    /**
     * Get list of berita as JSON.
     */
    public function getBerita(): JsonResponse
    {
        $berita = Cache::remember('berita-list', 3600, function () {
            $response = Http::get('https://api.sbh.ac.id/wp-json/wp/v2/posts', [

                'per_page' => 30,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->failed()) {
                Log::error("Gagal mengambil data berita dari API WordPress");
                return [];
            }

            // Tampilkan data mentah $post untuk debug
            // foreach ($response->json() as $post) {
            //     dd($post);
            // }

            return collect($response->json())->map(fn($post) => $this->formatBerita($post));
        });

        return response()->json($berita);
    }

    // Ambil detail berita berdasarkan slug
        public function getDetailBerita($slug)
        {
            // Ambil berita utama berdasarkan slug
            $berita = Cache::remember("berita_wordpress_slug_{$slug}", 3600, function () use ($slug) {
                $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts", [
                    'slug' => $slug,
                    '_embed' => 1
                ]);

                if ($response->failed() || empty($response->json())) {
                    abort(404, 'Berita tidak ditemukan');
                }

                // API return array, ambil index 0
                return $this->formatBerita($response->json()[0], true);
            });

            // Ambil 10 berita terbaru atau populer
            $beritaTerkait = Cache::remember('berita_terkait', 3600, function () {
                $response = Http::get("https://api.sbh.ac.id/wp-json/wp/v2/posts?per_page=10&_embed");

                if ($response->failed()) {
                    return [];
                }

                return collect($response->json())->map(function ($post) {
                    return [
                        'id' => $post['id'],
                        'title' => $post['title']['rendered'],
                        'slug' => $post['slug'],
                        'date' => Carbon::parse($post['date'])->translatedFormat('d F Y'),
                        'link' => $post['link'],
                        'image' => isset($post['_embedded']['wp:featuredmedia'][0]['source_url']) ?
                            $post['_embedded']['wp:featuredmedia'][0]['source_url'] :
                            asset('assets/img/no-image.jpg'),
                    ];
                });
            });

            return view('berita.detail', compact('berita', 'beritaTerkait'));
        }


    // Format data berita untuk list atau detail
    private function formatBerita($post, $includeContent = false)
    {
        // dd($post); // Tambahkan ini untuk lihat data mentah

        return [
            'id' => $post['id'] ?? null,
            'title' => isset($post['title']['rendered']) ? html_entity_decode($post['title']['rendered']) : '',
            'slug' => $post['slug'] ?? 'tes',
            'date' => isset($post['date']) ? Carbon::parse($post['date'])->translatedFormat('d F Y') : '',
            'link' => $post['link'] ?? '',
            'image' => $this->getFeaturedImage($post),
            'content' => $includeContent
                ? $this->cleanElementorSliderFromContent(html_entity_decode($post['content']['rendered'] ?? ''))
                : null,
        ];
    }

    private function getFeaturedImage($post)
    {
        // Cek jika sudah ada _embedded media (WordPress API bisa return _embedded)
        if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            return $post['_embedded']['wp:featuredmedia'][0]['source_url'];
        }

        // Fallback: cek _links
        if (isset($post['_links']['wp:featuredmedia'][0]['href'])) {
            try {
                $mediaResponse = Http::get($post['_links']['wp:featuredmedia'][0]['href']);
                if ($mediaResponse->successful()) {
                    $media = $mediaResponse->json();
                    return $media['source_url'] ?? asset('assets/img/no-image.jpg');
                }
            } catch (\Exception $e) {
                Log::error("Gagal mengambil gambar berita: " . $e->getMessage());
            }
        }

        // Default jika tidak ada gambar
        return asset('assets/img/no-image.jpg');
    }
    private function cleanElementorSliderFromContent($content)
    {
        libxml_use_internal_errors(true); // Supaya tidak error kalau HTML kurang rapi
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new DOMXPath($dom);
        foreach ($xpath->query("//div[contains(@class, 'elementor-widget-slider')]") as $node) {
            $node->parentNode->removeChild($node);
        }

        return $dom->saveHTML();
    }

}