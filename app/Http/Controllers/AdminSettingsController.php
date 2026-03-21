<?php

namespace App\Http\Controllers;

use App\Mail\SmtpTestMail;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(): View
    {
        $smtp = AppSetting::smtpConfig();

        return view('admin.settings.edit', [
            'smtp' => $smtp,
            'smtpPasswordMissing' => filled($smtp['host']) && blank($smtp['password']),
            'emailAlerts' => AppSetting::emailAlertConfig(),
            'siteLogoUrl' => AppSetting::siteLogoUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_logo' => ['nullable', 'file', 'max:2048', 'mimes:png,jpg,jpeg,webp,svg'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['required', 'email', 'max:255'],
            'smtp_from_name' => ['required', 'string', 'max:255'],
            'email_alert_employee_update_submitted_subject' => ['nullable', 'string', 'max:255'],
            'email_alert_employee_update_submitted_body' => ['nullable', 'string', 'max:2000'],
            'email_alert_employee_update_submitted_action' => ['nullable', 'string', 'max:255'],
            'email_alert_update_reviewed_subject' => ['nullable', 'string', 'max:255'],
            'email_alert_update_reviewed_body' => ['nullable', 'string', 'max:2000'],
            'email_alert_update_reviewed_action' => ['nullable', 'string', 'max:255'],
            'email_alert_live_update_subject' => ['nullable', 'string', 'max:255'],
            'email_alert_live_update_body' => ['nullable', 'string', 'max:2000'],
            'email_alert_live_update_action' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('site_logo')) {
            $this->deleteStoredSiteLogo();
            $path = $request->file('site_logo')->store('site-logos', 'public');
            AppSetting::setValue('site_logo_path', $path);
        } elseif ($request->boolean('remove_site_logo')) {
            $this->deleteStoredSiteLogo();
            AppSetting::setValue('site_logo_path', '');
        }

        $smtpHost = strtolower(trim($validated['smtp_host']));
        $smtpUsername = isset($validated['smtp_username']) ? trim($validated['smtp_username']) : '';
        $smtpPassword = isset($validated['smtp_password']) ? preg_replace('/\s+/', '', $validated['smtp_password']) : '';
        $smtpFromAddress = trim($validated['smtp_from_address']);
        $smtpFromName = trim($validated['smtp_from_name']);

        if ($smtpHost === 'smtp.gmail.com' && $smtpUsername !== '' && $smtpFromAddress === '') {
            $smtpFromAddress = $smtpUsername;
        }

        AppSetting::setValue('smtp_host', $smtpHost);
        AppSetting::setValue('smtp_port', (string) $validated['smtp_port']);
        AppSetting::setValue('smtp_encryption', $validated['smtp_encryption'] === 'none' ? '' : ($validated['smtp_encryption'] ?? ''));
        AppSetting::setValue('smtp_username', $smtpUsername);

        if (! empty($smtpPassword)) {
            AppSetting::setValue('smtp_password', Crypt::encryptString($smtpPassword));
        }

        AppSetting::setValue('smtp_from_address', $smtpFromAddress);
        AppSetting::setValue('smtp_from_name', $smtpFromName);

        $emailAlertDefaults = AppSetting::emailAlertDefaults();
        foreach ($emailAlertDefaults as $key => $default) {
            $inputKey = 'email_alert_'.$key;
            $value = isset($validated[$inputKey]) ? trim((string) $validated[$inputKey]) : '';
            AppSetting::setValue($inputKey, $value === '' ? $default : $value);
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    private function deleteStoredSiteLogo(): void
    {
        $path = AppSetting::siteLogoPath();
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $smtp = AppSetting::smtpConfig();

        if (filled($smtp['host']) && blank($smtp['password'])) {
            return back()->withErrors([
                'test_email' => 'No SMTP password is available (field left blank or password could not be decrypted with this APP_KEY). Enter the SMTP password, click Save Settings, then send the test again.',
            ])->withInput();
        }

        try {
            Mail::to($validated['test_email'])->send(new SmtpTestMail((string) config('app.name')));
        } catch (\Throwable $exception) {
            $message = 'Failed to send test email: '.$exception->getMessage();
            $smtpHost = strtolower((string) AppSetting::getValue('smtp_host', ''));

            if (str_contains($message, '535')) {
                if (str_contains($smtpHost, 'gmail')) {
                    $message = 'SMTP authentication failed (535). For Gmail, use smtp.gmail.com, port 587, TLS, your full Gmail address as the username, and an App Password (Google Account → Security → 2-Step Verification → App passwords)—not your normal Gmail password.';
                } elseif (str_contains($smtpHost, 'office365') || str_contains($smtpHost, 'outlook')) {
                    $message = 'SMTP authentication failed (535). For Microsoft 365, try smtp.office365.com, port 587, TLS, username = your full email, and the account password (or an app password if required). Your organization may disable SMTP AUTH or basic auth—in that case this mailbox cannot log in via SMTP until an admin enables it.';
                } else {
                    $message = 'SMTP authentication failed (535). Confirm username (often the full email), password, host, port, and encryption match your provider. Re-enter the SMTP password and Save Settings if you recently changed APP_KEY or imported the database.';
                }
            }

            return back()->withErrors([
                'test_email' => $message,
            ])->withInput();
        }

        return back()->with('success', 'Test email sent to '.$validated['test_email'].'.');
    }

    public function runDiagnostics(): RedirectResponse
    {
        $host = trim((string) AppSetting::getValue('smtp_host', ''));
        $port = (int) AppSetting::getValue('smtp_port', '587');
        $encryption = trim((string) AppSetting::getValue('smtp_encryption', 'tls'));
        $username = trim((string) AppSetting::getValue('smtp_username', ''));
        $hasPassword = AppSetting::getValue('smtp_password') !== null;

        if ($host === '' || $port <= 0) {
            return back()->withErrors([
                'test_email' => 'Run diagnostics failed: SMTP host/port are missing. Save SMTP settings first.',
            ]);
        }

        $connectHost = $encryption === 'ssl' ? 'ssl://'.$host : $host;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($connectHost.':'.$port, $errno, $errstr, 8, STREAM_CLIENT_CONNECT);

        $diagnostics = [
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption === '' ? 'none' : $encryption,
            'username' => $username !== '' ? $username : '(not set)',
            'password_set' => $hasPassword,
            'reachable' => is_resource($socket),
            'error' => is_resource($socket)
                ? null
                : (trim($errstr) !== '' ? trim($errstr) : ('Connection failed with code '.$errno)),
            'hint' => null,
        ];

        if (is_resource($socket)) {
            fclose($socket);
            $diagnostics['hint'] = 'SMTP server is reachable. If sending still fails, verify username/password and SMTP AUTH settings with your provider.';
        } else {
            $diagnostics['hint'] = 'SMTP server could not be reached. Check host, port, encryption, firewall rules, and provider network restrictions.';
        }

        return back()->with('smtp_diagnostics', $diagnostics)->with('success', 'SMTP diagnostics completed.');
    }
}
