<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\LandingMediaController;
use App\Models\LandingMedia;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingMediaUploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_landing_image_can_be_uploaded_and_saved(): void
    {
        Storage::fake('public');

        $media = LandingMedia::query()->create([
            'key' => 'upload-test',
            'label' => 'Upload Test',
            'path' => LandingMedia::DEFAULT_IMAGE,
        ]);

        $request = Request::create('/admin/landing-media/' . $media->id, 'PUT');
        $request->files->set('image', UploadedFile::fake()->image('hero.webp', 1200, 800)->size(500));

        app(LandingMediaController::class)->update($request, $media);

        $media->refresh();

        $this->assertStringStartsWith('landing-media/upload-test-', $media->path);
        Storage::disk('public')->assertExists($media->path);
        $this->assertStringContainsString('/storage/landing-media/', $media->url);
        $this->assertSame('WEBP', $media->file_format);
        $this->assertSame('1200 × 800 px', $media->dimensions);
        $this->assertNotSame('File tidak ditemukan', $media->file_size_label);
    }
}
