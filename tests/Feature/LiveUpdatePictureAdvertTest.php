<?php

namespace Tests\Feature;

use App\Models\AnnouncementAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveUpdatePictureAdvertTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_publish_live_update_with_picture_advert(): void
    {
        Storage::fake('public');

        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $image = UploadedFile::fake()->image('promo-banner.jpg', 1200, 600);

        $response = $this->actingAs($employer)->post(route('announcements.store'), [
            'title' => 'Promo live update',
            'body' => 'Check our latest campaign.',
            'target_type' => 'all',
            'picture_adverts' => [$image],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('announcements', [
            'title' => 'Promo live update',
        ]);

        $this->assertDatabaseCount('announcement_attachments', 1);

        $storedPath = AnnouncementAttachment::query()->value('file_path');
        Storage::disk('public')->assertExists($storedPath);
    }
}
