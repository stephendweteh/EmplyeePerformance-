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

            $enc = strtolower(trim((string) ($smtp['encryption'] ?? '')));
            $enc = $enc === '' ? 'none' : $enc;
            $port = (int) ($smtp['port'] ?? 587);

            // Symfony Mailer uses `scheme` (smtp vs smtps) and `auto_tls`, not Laravel's legacy `encryption` key.
            if ($enc === 'none') {
                $scheme = $port === 465 ? 'smtps' : 'smtp';
                $autoTls = false;
            } elseif ($enc === 'ssl') {
                $scheme = 'smtps';
                $autoTls = false;
            } else {
                $scheme = $port === 465 ? 'smtps' : 'smtp';
                $autoTls = $scheme === 'smtp';
            }

            $base = config('mail.mailers.smtp', []);

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => array_merge($base, [
                    'transport' => 'smtp',
                    'url' => null,
                    'scheme' => $scheme,
                    'host' => $smtp['host'],
                    'port' => $port,
                    'username' => $smtp['username'] ?: null,
                    'password' => $smtp['password'] ?: null,
                    'auto_tls' => $autoTls,
                ]),
                'mail.from.address' => $smtp['from_address'] ?: config('mail.from.address'),
                'mail.from.name' => $smtp['from_name'] ?: config('mail.from.name'),
            ]);
        } catch (\Throwable) {
            // Skip DB-backed SMTP config if the database is not reachable during bootstrap.
        }
    }
}
