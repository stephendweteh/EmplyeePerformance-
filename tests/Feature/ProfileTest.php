<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+15551234567',
                'job_title' => 'Software Engineer',
                'bio' => 'I build internal tools.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('+15551234567', $user->phone);
        $this->assertSame('Software Engineer', $user->job_title);
        $this->assertSame('I build internal tools.', $user->bio);
        $this->assertNull($user->email_verified_at);
    }

    public function test_employee_and_employer_can_manage_their_profile_fields(): void
    {
        foreach (['employee', 'employer'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this
                ->actingAs($user)
                ->patch('/profile', [
                    'name' => ucfirst($role).' User',
                    'email' => $role.'.profile@example.com',
                    'phone' => '+15550000000',
                    'job_title' => 'Team Lead',
                    'bio' => 'Updated profile for '.$role,
                ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect('/profile');

            $user->refresh();

            $this->assertSame(ucfirst($role).' User', $user->name);
            $this->assertSame($role.'.profile@example.com', $user->email);
            $this->assertSame('+15550000000', $user->phone);
            $this->assertSame('Team Lead', $user->job_title);
            $this->assertSame('Updated profile for '.$role, $user->bio);
        }
    }

    public function test_user_can_upload_profile_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('avatar.jpg', 120, 120);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'profile_photo' => $photo,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
