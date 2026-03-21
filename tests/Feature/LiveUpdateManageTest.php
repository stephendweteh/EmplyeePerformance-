<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AnnouncementTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveUpdateManageTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_edit_live_update(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $announcement = Announcement::create([
            'author_id' => $employer->id,
            'title' => 'Original title',
            'body' => 'Original body',
        ]);

        AnnouncementTarget::create([
            'announcement_id' => $announcement->id,
            'target_type' => 'all',
            'target_id' => null,
        ]);

        $response = $this->actingAs($employer)->put(route('announcements.update', $announcement), [
            'title' => 'Updated title',
            'body' => 'Updated body',
            'target_type' => 'all',
        ]);

        $response->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated title',
            'body' => 'Updated body',
        ]);
    }

    public function test_author_can_remove_existing_files_and_add_images_when_editing_live_update(): void
    {
        Storage::fake('public');

        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $announcement = Announcement::create([
            'author_id' => $employer->id,
            'title' => 'With files',
            'body' => 'Body',
        ]);

        AnnouncementTarget::create([
            'announcement_id' => $announcement->id,
            'target_type' => 'all',
            'target_id' => null,
        ]);

        $existing = AnnouncementAttachment::create([
            'announcement_id' => $announcement->id,
            'file_name' => 'old.png',
            'file_path' => 'announcement-attachments/old.png',
            'mime_type' => 'image/png',
            'size_bytes' => 8,
        ]);
        Storage::disk('public')->put($existing->file_path, 'fake-png');

        $newImage = UploadedFile::fake()->image('new-banner.jpg', 400, 300);

        $response = $this->actingAs($employer)->put(route('announcements.update', $announcement), [
            'title' => 'With files',
            'body' => 'Body updated',
            'target_type' => 'all',
            'remove_attachment_ids' => [$existing->id],
            'picture_adverts' => [$newImage],
        ]);

        $response->assertRedirect(route('announcements.index'));

        $this->assertDatabaseMissing('announcement_attachments', ['id' => $existing->id]);
        Storage::disk('public')->assertMissing($existing->file_path);

        $this->assertDatabaseCount('announcement_attachments', 1);
        $newPath = AnnouncementAttachment::query()->value('file_path');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_author_can_delete_live_update(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $announcement = Announcement::create([
            'author_id' => $employer->id,
            'title' => 'To remove',
            'body' => 'Will be deleted',
        ]);

        $response = $this->actingAs($employer)->delete(route('announcements.destroy', $announcement));

        $response->assertRedirect(route('announcements.index'));

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_employee_cannot_edit_or_delete_live_update(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $announcement = Announcement::create([
            'author_id' => $employer->id,
            'title' => 'Protected live update',
            'body' => 'Not editable by employee',
        ]);

        $editResponse = $this->actingAs($employee)->put(route('announcements.update', $announcement), [
            'title' => 'Hack',
            'body' => 'Hack',
            'target_type' => 'all',
        ]);

        $deleteResponse = $this->actingAs($employee)->delete(route('announcements.destroy', $announcement));

        $editResponse->assertForbidden();
        $deleteResponse->assertForbidden();
    }
}
