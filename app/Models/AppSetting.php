<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return self::query()->where('key', $key)->value('value') ?? $default;
    }

    /** Public disk path relative to storage/app/public, or null when using the default SVG logo. */
    public static function siteLogoPath(): ?string
    {
        if (! Schema::hasTable('app_settings')) {
            return null;
        }

        $path = self::getValue('site_logo_path');

        return ($path === null || $path === '') ? null : $path;
    }

    public static function siteLogoUrl(): ?string
    {
        $path = self::siteLogoPath();

        return $path === null ? null : PublicStorage::url($path);
    }

    public static function setValue(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function smtpConfig(): array
    {
        $encryptedPassword = self::getValue('smtp_password');

        return [
            'host' => self::getValue('smtp_host'),
            'port' => self::getValue('smtp_port', '587'),
            'encryption' => self::getValue('smtp_encryption', 'tls'),
            'username' => self::getValue('smtp_username'),
            'password' => self::decryptSmtpPassword($encryptedPassword),
            'from_address' => self::getValue('smtp_from_address'),
            'from_name' => self::getValue('smtp_from_name'),
        ];
    }

    private static function decryptSmtpPassword(?string $encrypted): ?string
    {
        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            Log::warning('smtp_password in app_settings could not be decrypted (wrong or rotated APP_KEY). Re-save SMTP password in Admin → Settings.');

            return null;
        }
    }

    public static function emailAlertDefaults(): array
    {
        return [
            'employee_update_submitted_subject' => 'New employee daily update submitted',
            'employee_update_submitted_body' => ':employee_name submitted a daily update for :date (:team).',
            'employee_update_submitted_action' => 'Review Updates',
            'update_reviewed_subject' => 'Your daily update was reviewed',
            'update_reviewed_body' => 'Your manager reviewed your daily update. Rating: :rating/10. Status: :status. Comment: :comment.',
            'update_reviewed_action' => 'View Dashboard',
            'live_update_subject' => 'New live update: :title',
            'live_update_body' => 'A new company update has been published: :title.',
            'live_update_action' => 'Read Live Update',
        ];
    }

    public static function emailAlertConfig(): array
    {
        $defaults = self::emailAlertDefaults();
        $config = [];

        foreach ($defaults as $key => $default) {
            $value = self::getValue('email_alert_'.$key, $default);
            $config[$key] = ($value === null || trim($value) === '') ? $default : $value;
        }

        return $config;
    }

    public static function renderTemplate(string $template, array $replacements = []): string
    {
        return strtr($template, $replacements);
    }
}
