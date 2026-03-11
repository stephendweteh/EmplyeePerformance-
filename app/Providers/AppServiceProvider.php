<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return;
            }

            $smtp = AppSetting::smtpConfig();
            if (empty($smtp['host'])) {
                return;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['host'],
                'mail.mailers.smtp.port' => (int) ($smtp['port'] ?? 587),
                'mail.mailers.smtp.encryption' => $smtp['encryption'] ?: null,
                'mail.mailers.smtp.username' => $smtp['username'] ?: null,
                'mail.mailers.smtp.password' => $smtp['password'] ?: null,
                'mail.from.address' => $smtp['from_address'] ?: config('mail.from.address'),
                'mail.from.name' => $smtp['from_name'] ?: config('mail.from.name'),
            ]);
        } catch (\Throwable) {
            // Skip DB-backed SMTP config if the database is not reachable during bootstrap.
        }
    }
}
