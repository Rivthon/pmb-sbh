<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingVideo;
use Illuminate\Http\Request;

class LandingVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = LandingVideo::orderBy('sort_order', 'asc')->get();
        return view('admin.landing-videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.landing-videos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'youtube_video_id' => 'required|string',
            'title'            => 'required|string|max:255',
            'sort_order'       => 'nullable|integer',
        ]);

        $videoId = $this->extractYoutubeId($request->youtube_video_id);

        LandingVideo::create([
            'youtube_video_id' => $videoId,
            'title'            => $request->title,
            'is_active'        => $request->has('is_active'),
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.landing-videos.index')
            ->with('success', 'Video Shorts berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandingVideo $landingVideo)
    {
        return view('admin.landing-videos.edit', compact('landingVideo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LandingVideo $landingVideo)
    {
        $request->validate([
            'youtube_video_id' => 'required|string',
            'title'            => 'required|string|max:255',
            'sort_order'       => 'nullable|integer',
        ]);

        $videoId = $this->extractYoutubeId($request->youtube_video_id);

        $landingVideo->update([
            'youtube_video_id' => $videoId,
            'title'            => $request->title,
            'is_active'        => $request->has('is_active'),
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.landing-videos.index')
            ->with('success', 'Video Shorts berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandingVideo $landingVideo)
    {
        $landingVideo->delete();

        return redirect()->route('admin.landing-videos.index')
            ->with('success', 'Video Shorts berhasil dihapus.');
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleActive(LandingVideo $landingVideo)
    {
        $landingVideo->update([
            'is_active' => !$landingVideo->is_active,
        ]);

        $status = $landingVideo->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.landing-videos.index')
            ->with('success', "Video Shorts berhasil {$status}.");
    }

    /**
     * Helper to extract YouTube Video ID from standard YouTube URLs or string IDs.
     */
    private function extractYoutubeId($urlOrId)
    {
        $urlOrId = trim($urlOrId);

        // If it's already an 11-character ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $urlOrId)) {
            return $urlOrId;
        }

        // Try extracting from full URL
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/)|youtu\.be/)([^"&?/ ]{11})%i', $urlOrId, $match)) {
            return $match[1];
        }

        return $urlOrId;
    }
}
