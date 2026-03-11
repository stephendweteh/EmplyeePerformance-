<?php

namespace Tests\Feature;

use App\Models\EmployeeUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeUpdateAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_upload_attachments_with_daily_update(): void
    {
        Storage::fake('public');

        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $fileA = UploadedFile::fake()->create('win-summary.pdf', 200, 'application/pdf');
        $fileB = UploadedFile::fake()->image('screenshot.png');

        $response = $this->actingAs($employee)->post(route('employee-updates.store'), [
            'date' => now()->toDateString(),
            'wins' => 'Completed quarterly report.',
            'business_impact' => 'Improved visibility for stakeholders.',
            'blockers' => 'None',
            'tags' => 'reporting,delivery',
            'attachments' => [$fileA, $fileB],
        ]);

        $response->assertSessionHasNoErrors();

        $update = EmployeeUpdate::query()->firstOrFail();

        $this->assertCount(2, $update->attachments);

        foreach ($update->attachments as $attachment) {
            Storage::disk('public')->assertExists($attachment->file_path);
        }
    }
}
