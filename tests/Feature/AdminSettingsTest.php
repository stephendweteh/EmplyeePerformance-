<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_settings_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.edit'));

        $response->assertOk();
        $response->assertSee('Site logo');
        $response->assertSee('SMTP Settings');
    }

    public function test_settings_page_loads_when_smtp_password_was_encrypted_with_different_app_key(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        AppSetting::setValue('smtp_password', 'eyJpdiI6IkZha2UiLCJ2YWx1ZSI6IkZha2UiLCJtYWMiOiJmYWtlbWFjIiwidGFnIjoiIn0=');

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.edit'));

        $response->assertOk();
        $this->assertNull(AppSetting::smtpConfig()['password']);
    }

    public function test_super_admin_can_save_smtp_settings(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), [
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'secret-pass',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'DaddyAsh',
            'email_alert_employee_update_submitted_subject' => 'Employee update from :employee_name',
            'email_alert_employee_update_submitted_body' => ':employee_name posted on :date',
            'email_alert_employee_update_submitted_action' => 'Open Employer Dashboard',
            'email_alert_update_reviewed_subject' => 'Review posted',
            'email_alert_update_reviewed_body' => 'Status :status, rating :rating',
            'email_alert_update_reviewed_action' => 'Open Employee Dashboard',
            'email_alert_live_update_subject' => 'Live item: :title',
            'email_alert_live_update_body' => 'Please read: :title',
            'email_alert_live_update_action' => 'Open Live Updates',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertSame('smtp.example.com', AppSetting::getValue('smtp_host'));
        $this->assertSame('587', AppSetting::getValue('smtp_port'));
        $this->assertSame('tls', AppSetting::getValue('smtp_encryption'));
        $this->assertSame('smtp-user', AppSetting::getValue('smtp_username'));
        $this->assertSame('noreply@example.com', AppSetting::getValue('smtp_from_address'));
        $this->assertSame('DaddyAsh', AppSetting::getValue('smtp_from_name'));
        $this->assertNotNull(AppSetting::getValue('smtp_password'));
        $this->assertSame('Employee update from :employee_name', AppSetting::getValue('email_alert_employee_update_submitted_subject'));
        $this->assertSame('Status :status, rating :rating', AppSetting::getValue('email_alert_update_reviewed_body'));
        $this->assertSame('Open Live Updates', AppSetting::getValue('email_alert_live_update_action'));
    }

    public function test_super_admin_can_upload_site_logo(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $logo = UploadedFile::fake()->image('brand.png', 200, 60);

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), [
            'site_logo' => $logo,
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'secret-pass',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'DaddyAsh',
            'email_alert_employee_update_submitted_subject' => 'Employee update from :employee_name',
            'email_alert_employee_update_submitted_body' => ':employee_name posted on :date',
            'email_alert_employee_update_submitted_action' => 'Open Employer Dashboard',
            'email_alert_update_reviewed_subject' => 'Review posted',
            'email_alert_update_reviewed_body' => 'Status :status, rating :rating',
            'email_alert_update_reviewed_action' => 'Open Employee Dashboard',
            'email_alert_live_update_subject' => 'Live item: :title',
            'email_alert_live_update_body' => 'Please read: :title',
            'email_alert_live_update_action' => 'Open Live Updates',
        ]);

        $response->assertSessionHasNoErrors();

        $storedPath = AppSetting::getValue('site_logo_path');
        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);
        $this->assertNotNull(AppSetting::siteLogoUrl());
    }

    public function test_super_admin_can_remove_site_logo(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        AppSetting::setValue('site_logo_path', 'site-logos/old.png');
        Storage::disk('public')->put('site-logos/old.png', 'fake');

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), [
            'remove_site_logo' => '1',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'secret-pass',
            'smtp_from_address' => 'noreply@example.com',
            'smtp_from_name' => 'DaddyAsh',
            'email_alert_employee_update_submitted_subject' => 'Employee update from :employee_name',
            'email_alert_employee_update_submitted_body' => ':employee_name posted on :date',
            'email_alert_employee_update_submitted_action' => 'Open Employer Dashboard',
            'email_alert_update_reviewed_subject' => 'Review posted',
            'email_alert_update_reviewed_body' => 'Status :status, rating :rating',
            'email_alert_update_reviewed_action' => 'Open Employee Dashboard',
            'email_alert_live_update_subject' => 'Live item: :title',
            'email_alert_live_update_body' => 'Please read: :title',
            'email_alert_live_update_action' => 'Open Live Updates',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('', AppSetting::getValue('site_logo_path'));
        Storage::disk('public')->assertMissing('site-logos/old.png');
        $this->assertNull(AppSetting::siteLogoUrl());
    }

    public function test_super_admin_can_send_test_email(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.settings.test-email'), [
            'test_email' => 'notify@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        Mail::assertSent(SmtpTestMail::class, function (SmtpTestMail $mail) {
            return $mail->hasTo('notify@example.com');
        });
    }

    public function test_super_admin_can_run_smtp_diagnostics(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        AppSetting::setValue('smtp_host', 'localhost');
        AppSetting::setValue('smtp_port', '2525');
        AppSetting::setValue('smtp_encryption', 'none');
        AppSetting::setValue('smtp_username', 'user@example.com');
        AppSetting::setValue('smtp_password', 'encrypted');

        $response = $this->actingAs($superAdmin)->post(route('admin.settings.diagnostics'));

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('smtp_diagnostics');
    }
}
