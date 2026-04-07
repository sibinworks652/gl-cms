<?php

namespace Modules\Settings;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Models\Setting;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'settings');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');

        $this->applyRuntimeSettings();
    }

    protected function applyRuntimeSettings(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $settings = Setting::pairs();

            if ($settings === []) {
                return;
            }

            $smtpScheme = $this->resolveSmtpScheme($settings['mail_encryption'] ?? null);
            $smtpPassword = $this->decryptSettingValue($settings['mail_password'] ?? null);

            config([
                'app.name' => $settings['site_name'] ?? config('app.name'),
                'app.env' => $settings['app_env'] ?? config('app.env'),
                'app.debug' => $this->toBoolean($settings['debug_mode'] ?? config('app.debug')),
                'app.url' => $settings['app_url'] ?? config('app.url'),
                'app.timezone' => $settings['timezone'] ?? config('app.timezone'),
                'mail.default' => $settings['mail_mailer'] ?? config('mail.default'),
                'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $smtpPassword ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.scheme' => $smtpScheme,
                'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
                'mail.enquiry_to' => $settings['site_email'] ?? config('mail.enquiry_to'),
                'settings.cache_enabled' => $this->toBoolean($settings['cache_enabled'] ?? true),
            ]);

            if (! empty($settings['timezone'])) {
                date_default_timezone_set($settings['timezone']);
            }

            $this->syncMaintenanceMode($settings);

            $sharedSettings = $settings;

            if (! empty($sharedSettings['mail_password'])) {
                $sharedSettings['mail_password'] = '********';
            }

            view()->share('siteSettings', $sharedSettings);
        } catch (\Throwable) {
            //
        }
    }

    protected function resolveSmtpScheme(?string $encryption): ?string
    {
        return match (strtolower(trim((string) $encryption))) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => config('mail.mailers.smtp.scheme'),
        };
    }

    protected function decryptSettingValue(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function syncMaintenanceMode(array $settings): void
    {
        $maintenanceEnabled = $this->toBoolean($settings['maintenance_mode'] ?? false);
        $downFileExists = app()->isDownForMaintenance();

        if ($maintenanceEnabled && ! $downFileExists) {
            Artisan::call('down');

            return;
        }

        if (! $maintenanceEnabled && $downFileExists) {
            Artisan::call('up');
        }
    }

    protected function toBoolean(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }
}
