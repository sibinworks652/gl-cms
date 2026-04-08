<?php

namespace Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Settings\Models\Setting;

class SettingsController extends Controller
{
    protected const SECRET_MASK = '********';

    public function show()
    {
       abort_unless(auth('admin')->user()?->can('settings.view'), 403, 'You do not have permission to access this resource.');

        $sections = $this->sections();
        $activeSection = request()->query('section');

        return view('settings::view', [
            'settings' => $this->settingsForDisplay(),
            'sections' => $sections,
            'activeSection' => array_key_exists((string) $activeSection, $sections) ? (string) $activeSection : null,
        ]);
    }

    public function edit()
    {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403, 'You do not have permission to access this resource.');

        return $this->renderEditForm(null);
    }

    public function update(Request $request)
    {
        abort_unless(auth('admin')->user()?->can('settings.update'), 403, 'You do not have permission to access this resource.');

        $validated = $request->validate($this->rules());
        $this->persistSettings($request, $validated, null);

        return redirect()
            ->route('admin.settings.show')
            ->with('success', 'Settings updated successfully.');
    }

    public function editSection(string $section)
    {
        $section = $this->normalizeSection($section);
        $this->authorizeSectionUpdate($section);

        return $this->renderEditForm($section);
    }

    public function updateSection(Request $request, string $section)
    {
        $section = $this->normalizeSection($section);
        $this->authorizeSectionUpdate($section);

        if ($request->input('section_action') === 'test_mail' && $section === 'mail') {
            return $this->handleMailTest($request);
        }

        if ($request->input('section_action') === 'validate_analytics' && $section === 'analytics') {
            return $this->handleAnalyticsValidation();
        }

        $validated = $request->validate($this->rules($section));

        $this->persistSettings($request, $validated, $section);

        return redirect()
            ->route('admin.settings.show', ['section' => $section])
            ->with('success', $this->sections()[$section]['title'] . ' updated successfully.');
    }

    protected function handleMailTest(Request $request)
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            Mail::raw(
                'This is a test email from the Settings module SMTP connection check.',
                function ($message) use ($validated) {
                    $message->to($validated['test_email'])
                        ->subject('SMTP Test Email');
                }
            );

            return redirect()
                ->route('admin.settings.section.edit', 'mail')
                ->with('mail_test_status', [
                    'type' => 'success',
                    'message' => 'SMTP test email sent successfully to ' . $validated['test_email'] . '.',
                ]);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.settings.section.edit', 'mail')
                ->withInput()
                ->with('mail_test_status', [
                    'type' => 'danger',
                    'message' => 'SMTP test failed: ' . $exception->getMessage(),
                ]);
        }
    }

    protected function handleAnalyticsValidation()
    {
        $settings = Setting::pairs();
        $results = [
            'google_analytics_id' => $this->analyticsResult(
                'Google Analytics ID',
                $settings['google_analytics_id'] ?? null,
                '/^(G|UA)-[A-Z0-9\-]+$/i'
            ),
            'google_tag_manager_id' => $this->analyticsResult(
                'Google Tag Manager ID',
                $settings['google_tag_manager_id'] ?? null,
                '/^GTM-[A-Z0-9]+$/i'
            ),
            'facebook_pixel_id' => $this->analyticsResult(
                'Facebook Pixel ID',
                $settings['facebook_pixel_id'] ?? null,
                '/^[0-9]{8,20}$/'
            ),
        ];

        return redirect()
            ->route('admin.settings.section.edit', 'analytics')
            ->with('analytics_validation', $results);
    }

    protected function renderEditForm(?string $section)
    {
        $sections = $this->sections();
        $activeSection = $section ? $this->normalizeSection($section) : null;

        return view('settings::form', [
            'settings' => $this->settingsForEdit(),
            'sections' => $sections,
            'activeSection' => $activeSection,
            'timezones' => timezone_identifiers_list(),
            'mailers' => ['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log'],
            'encryptions' => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'],
        ]);
    }

    protected function persistSettings(Request $request, array $validated, ?string $onlySection): void
    {
        DB::transaction(function () use ($request, $validated, $onlySection) {
            $sections = $onlySection
                ? [$onlySection => $this->sections()[$onlySection]]
                : $this->sections();

            foreach ($sections as $group => $section) {
                foreach ($section['fields'] as $key => $field) {
                    if (in_array($key, ['site_logo', 'site_favicon', 'admin_logo'], true)) {
                        continue;
                    }

                    if (in_array($key, $this->encryptedSettingKeys(), true)) {
                        if (! array_key_exists($key, $validated) || $this->shouldKeepSecret($validated[$key])) {
                            $this->encryptExistingSecretIfNeeded($key, $group);

                            continue;
                        }

                        Setting::updateOrCreate(
                            ['key' => $key],
                            [
                                'group' => $group,
                                'value' => Crypt::encryptString((string) $validated[$key]),
                            ]
                        );

                        continue;
                    }

                    Setting::updateOrCreate(
                        ['key' => $key],
                        [
                            'group' => $group,
                            'value' => isset($validated[$key]) ? (string) $validated[$key] : null,
                        ]
                    );
                }
            }

            if (! $onlySection || $onlySection === 'general') {
                $this->storeUploadedSetting($request, 'site_logo', 'settings/site', 'general');
                $this->storeUploadedSetting($request, 'site_favicon', 'settings/site', 'general');
            }

            if (! $onlySection || $onlySection === 'admin') {
                $this->storeUploadedSetting($request, 'admin_logo', 'settings/admin', 'admin');
            }
        });

        Setting::clearCache();

        $settings = Setting::freshPairs();

        $this->syncEnvironmentSettings($settings);
        $this->syncRuntimeConfiguration($settings);
        $this->syncMaintenanceMode($settings);

        if (($settings['cache_enabled'] ?? '1') === '1') {
            Setting::pairs();
        }
    }

    protected function settingsForDisplay(): array
    {
        $settings = Setting::pairs();

        foreach ($this->encryptedSettingKeys() as $key) {
            if (! empty($settings[$key])) {
                $settings[$key] = self::SECRET_MASK;
            }
        }

        return $settings;
    }

    protected function settingsForEdit(): array
    {
        $settings = Setting::pairs();

        foreach ($this->encryptedSettingKeys() as $key) {
            if (! empty($settings[$key])) {
                $settings[$key] = $this->decryptSettingValue($settings[$key]) ?? '';
            }
        }

        return $settings;
    }

    protected function encryptedSettingKeys(): array
    {
        return ['mail_password'];
    }

    protected function shouldKeepSecret(mixed $value): bool
    {
        $value = trim((string) $value);

        return $value === '' || $value === self::SECRET_MASK;
    }

    protected function encryptExistingSecretIfNeeded(string $key, string $group): void
    {
        $value = Setting::query()->where('key', $key)->value('value');

        if (blank($value) || $this->isEncryptedSettingValue((string) $value)) {
            return;
        }

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => Crypt::encryptString((string) $value),
            ]
        );
    }

    protected function isEncryptedSettingValue(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function normalizeSection(string $section): string
    {
        abort_unless(array_key_exists($section, $this->sections()), 404, 'Section not found.');

        return $section;
    }

    protected function authorizeSectionUpdate(string $section): void
    {
        $permission = $this->sectionPermission($section);

        abort_unless(auth('admin')->user()?->can($permission), 403, 'You do not have permission to access this resource.');
    }

    protected function sectionPermission(string $section): string
    {
        return 'settings.' . $section . '.update';
    }

    protected function analyticsResult(string $label, ?string $value, string $pattern): array
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return [
                'label' => $label,
                'status' => 'warning',
                'message' => 'Not configured.',
            ];
        }

        if (! preg_match($pattern, $trimmed)) {
            return [
                'label' => $label,
                'status' => 'danger',
                'message' => 'Configured, but the format looks invalid: ' . $trimmed,
            ];
        }

        return [
            'label' => $label,
            'status' => 'success',
            'message' => 'Configured and format looks valid: ' . $trimmed,
        ];
    }

    protected function rules(?string $onlySection = null): array
    {
        $rules = [
            'mail_mailer' => ['nullable', Rule::in(['smtp', 'sendmail', 'log'])],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],

            'site_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:4096'],
            'site_favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,ico,svg,webp', 'max:2048'],
            'admin_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:4096'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'site_address' => ['nullable', 'string', 'max:1000'],
            'footer_copyright' => ['nullable', 'string', 'max:500'],
            'maintenance_mode' => ['nullable', Rule::in(['0', '1'])],
            'debug_mode' => ['nullable', Rule::in(['0', '1'])],
            'cache_enabled' => ['nullable', Rule::in(['0', '1'])],
            'app_env' => ['nullable', Rule::in(['local', 'development', 'staging', 'production'])],
            'app_url' => ['nullable', 'url', 'max:255'],
            'timezone' => ['nullable', Rule::in(timezone_identifiers_list())],
            'date_format' => ['nullable', 'string', 'max:50'],
            'time_format' => ['nullable', 'string', 'max:50'],
            'default_language' => ['nullable', 'string', 'max:10'],
            'custom_css' => ['nullable', 'string', 'max:20000'],
            'custom_js' => ['nullable', 'string', 'max:20000'],
            'admin_primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_topbar_bg' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_topbar_text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_sidebar_bg' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_sidebar_text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_sidebar_hover_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_page_bg' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'admin_dark_mode_enabled' => ['nullable', Rule::in(['0', '1'])],

            'facebook_url' => $this->socialUrlRules('Facebook', ['facebook.com', 'www.facebook.com']),
            'instagram_url' => $this->socialUrlRules('Instagram', ['instagram.com', 'www.instagram.com']),
            'twitter_url' => $this->socialUrlRules('Twitter', ['twitter.com', 'www.twitter.com', 'x.com', 'www.x.com']),
            'linkedin_url' => $this->socialUrlRules('LinkedIn', ['linkedin.com', 'www.linkedin.com']),
            'youtube_url' => $this->socialUrlRules('YouTube', ['youtube.com', 'www.youtube.com', 'youtu.be']),

            'google_analytics_id' => ['nullable', 'string', 'max:255'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:255'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:255'],
        ];

        if (! $onlySection) {
            return $rules;
        }

        return array_intersect_key($rules, $this->sections()[$onlySection]['fields']);
    }

    protected function socialUrlRules(string $platform, array $allowedHosts): array
    {
        return [
            'nullable',
            'url',
            'max:255',
            'starts_with:https://',
            function (string $attribute, mixed $value, \Closure $fail) use ($platform, $allowedHosts): void {
                if (blank($value)) {
                    return;
                }

                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                if ($host === '' || ! in_array($host, $allowedHosts, true)) {
                    $fail("The {$platform} URL must be a valid {$platform} profile link.");
                }
            },
        ];
    }

    protected function storeUploadedSetting(Request $request, string $key, string $directory, string $group): void
    {
        if (! $request->hasFile($key)) {
            return;
        }

        $existing = Setting::query()->where('key', $key)->value('value');

        if ($existing) {
            Storage::disk('public')->delete($existing);
        }

        $file = $request->file($key);
        $path = $file->storeAs($directory, $this->datedOriginalFilename($file), 'public');

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $path,
            ]
        );
    }

    protected function datedOriginalFilename(mixed $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'file';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }

    protected function sections(): array
    {
        return [
            'mail' => [
                'title' => 'Mail (SMTP) Settings',
                'description' => 'Outgoing mail delivery, sender identity, and authentication details.',
                'fields' => [
                    'mail_mailer' => ['label' => 'Mail Mailer', 'type' => 'text'],
                    'mail_host' => ['label' => 'Mail Host', 'type' => 'text'],
                    'mail_port' => ['label' => 'Mail Port', 'type' => 'text'],
                    'mail_username' => ['label' => 'Mail Username', 'type' => 'text'],
                    'mail_password' => ['label' => 'Mail Password', 'type' => 'password'],
                    'mail_encryption' => ['label' => 'Mail Encryption', 'type' => 'text'],
                    'mail_from_address' => ['label' => 'Mail From Address', 'type' => 'text'],
                    'mail_from_name' => ['label' => 'Mail From Name', 'type' => 'text'],
                ],
            ],
            'general' => [
                'title' => 'General Settings',
                'description' => 'Site identity, branding, contact details, and localization defaults.',
                'fields' => [
                    'site_name' => ['label' => 'Site Name', 'type' => 'text'],
                    'site_tagline' => ['label' => 'Site Tagline', 'type' => 'text'],
                    'site_logo' => ['label' => 'Site Logo', 'type' => 'image'],
                    'site_favicon' => ['label' => 'Site Favicon', 'type' => 'image'],
                    'site_email' => ['label' => 'Site Email', 'type' => 'text'],
                    'site_phone' => ['label' => 'Site Phone', 'type' => 'text'],
                    'site_address' => ['label' => 'Site Address', 'type' => 'textarea'],
                    'footer_copyright' => ['label' => 'Footer Copyright', 'type' => 'textarea'],
                    'timezone' => ['label' => 'Timezone', 'type' => 'text'],
                    'date_format' => ['label' => 'Date Format', 'type' => 'text'],
                    'time_format' => ['label' => 'Time Format', 'type' => 'text'],
                    'default_language' => ['label' => 'Default Language', 'type' => 'text'],
                    'custom_css' => ['label' => 'Custom CSS', 'type' => 'textarea'],
                    'custom_js' => ['label' => 'Custom JS', 'type' => 'textarea'],
                ],
            ],
            'system' => [
                'title' => 'System Settings',
                'description' => 'Application environment, URL, maintenance mode, debug mode, and cache behavior.',
                'fields' => [
                    'maintenance_mode' => ['label' => 'Maintenance Mode', 'type' => 'boolean'],
                    'debug_mode' => ['label' => 'Debug Mode', 'type' => 'boolean'],
                    'cache_enabled' => ['label' => 'Cache Enabled', 'type' => 'boolean'],
                    'app_env' => ['label' => 'App Environment', 'type' => 'text'],
                    'app_url' => ['label' => 'App URL', 'type' => 'url'],
                ],
            ],
            'admin' => [
                'title' => 'Admin Panel Settings',
                'description' => 'Admin branding and theme colors for the backend panel.',
                'fields' => [
                    'admin_logo' => ['label' => 'Admin Panel Logo', 'type' => 'image'],
                    'admin_primary_color' => ['label' => 'Admin Primary Color', 'type' => 'color'],
                    'admin_topbar_bg' => ['label' => 'Admin Topbar Background', 'type' => 'color'],
                    'admin_topbar_text_color' => ['label' => 'Admin Topbar Text Color', 'type' => 'color'],
                    'admin_sidebar_bg' => ['label' => 'Admin Sidebar Background', 'type' => 'color'],
                    'admin_sidebar_text_color' => ['label' => 'Admin Sidebar Text Color', 'type' => 'color'],
                    'admin_sidebar_hover_color' => ['label' => 'Admin Sidebar Hover Color', 'type' => 'color'],
                    'admin_page_bg' => ['label' => 'Admin Page Background', 'type' => 'color'],
                    'admin_dark_mode_enabled' => ['label' => 'Dark Mode', 'type' => 'boolean'],
                ],
            ],
            'social' => [
                'title' => 'Social Media Settings',
                'description' => 'Public profile links for header, footer, or contact areas.',
                'fields' => [
                    'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url'],
                    'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url'],
                    'twitter_url' => ['label' => 'Twitter URL', 'type' => 'url'],
                    'linkedin_url' => ['label' => 'LinkedIn URL', 'type' => 'url'],
                    'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url'],
                ],
            ],
            'analytics' => [
                'title' => 'Analytics Settings',
                'description' => 'Tracking and marketing IDs stored in one place for frontend use.',
                'fields' => [
                    'google_analytics_id' => ['label' => 'Google Analytics ID', 'type' => 'text'],
                    'google_tag_manager_id' => ['label' => 'Google Tag Manager ID', 'type' => 'text'],
                    'facebook_pixel_id' => ['label' => 'Facebook Pixel ID', 'type' => 'text'],
                ],
            ],
        ];
    }

    protected function syncEnvironmentSettings(array $settings): void
    {
        $envPath = base_path('.env');

        if (! is_file($envPath) || ! is_writable($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        if ($content === false) {
            return;
        }

        $replacements = [
            'APP_DEBUG' => $this->stringifyEnvBoolean($settings['debug_mode'] ?? '0'),
            'APP_ENV' => $settings['app_env'] ?? config('app.env'),
            'APP_URL' => $settings['app_url'] ?? config('app.url'),
        ];

        $updatedContent = $content;

        foreach ($replacements as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $updatedContent = $this->replaceEnvironmentValue($updatedContent, $key, (string) $value);
        }

        if ($updatedContent !== $content) {
            file_put_contents($envPath, $updatedContent);
            Artisan::call('config:clear');
        }
    }

    protected function syncRuntimeConfiguration(array $settings): void
    {
        config([
            'app.debug' => $this->toBoolean($settings['debug_mode'] ?? '0'),
            'app.env' => $settings['app_env'] ?? config('app.env'),
            'app.url' => $settings['app_url'] ?? config('app.url'),
            'mail.mailers.smtp.password' => $this->decryptSettingValue($settings['mail_password'] ?? null) ?? config('mail.mailers.smtp.password'),
            'settings.cache_enabled' => $this->toBoolean($settings['cache_enabled'] ?? '1'),
        ]);
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
        $maintenanceEnabled = $this->toBoolean($settings['maintenance_mode'] ?? '0');

        if ($maintenanceEnabled && ! app()->isDownForMaintenance()) {
            Artisan::call('down');

            return;
        }

        if (! $maintenanceEnabled && app()->isDownForMaintenance()) {
            Artisan::call('up');
        }
    }

    protected function replaceEnvironmentValue(string $content, string $key, string $value): string
    {
        $escapedKey = preg_quote($key, '/');
        $line = $key . '=' . $this->formatEnvironmentValue($value);

        if (preg_match('/^' . $escapedKey . '=.*$/m', $content)) {
            return preg_replace('/^' . $escapedKey . '=.*$/m', $line, $content) ?? $content;
        }

        return rtrim($content) . PHP_EOL . $line . PHP_EOL;
    }

    protected function formatEnvironmentValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s/', $value)) {
            return '"' . addcslashes($value, '"') . '"';
        }

        return $value;
    }

    protected function stringifyEnvBoolean(mixed $value): string
    {
        return $this->toBoolean($value) ? 'true' : 'false';
    }

    protected function toBoolean(mixed $value): bool
    {
        return in_array(Str::lower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }
}
