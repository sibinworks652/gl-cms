<?php

namespace App\Support;

class ModuleRegistry
{
    /**
     * @return array<string, array{name: string, namespace: string}>
     */
    public static function definitions(): array
    {
        return [
            'activity_logs' => ['name' => 'Activity Logs', 'namespace' => 'ActivityLogs'],
            'backup' => ['name' => 'Backup', 'namespace' => 'Backup'],
            'banner' => ['name' => 'Banner', 'namespace' => 'Banner'],
            'careers' => ['name' => 'Careers', 'namespace' => 'Careers'],
            'email' => ['name' => 'Email', 'namespace' => 'Email'],
            'faq' => ['name' => 'FAQ', 'namespace' => 'Faq'],
            'form_builder' => ['name' => 'Form Builder', 'namespace' => 'FormBuilder'],
            'gallery' => ['name' => 'Gallery', 'namespace' => 'Gallery'],
            'menu' => ['name' => 'Menu', 'namespace' => 'Menu'],
            'page' => ['name' => 'Page', 'namespace' => 'Page'],
            'seo' => ['name' => 'SEO', 'namespace' => 'Seo'],
            'services' => ['name' => 'Services', 'namespace' => 'Services'],
            'team' => ['name' => 'Team', 'namespace' => 'Team'],
            'testimonials' => ['name' => 'Testimonials', 'namespace' => 'Testimonials'],
        ];
    }

    public static function key(string $module): ?string
    {
        $normalized = strtolower(trim(str_replace([' ', '-'], '_', $module)));

        return array_key_exists($normalized, static::definitions()) ? $normalized : null;
    }

    public static function enabled(string $module): bool
    {
        $key = static::key($module);

        if (! $key || ! static::installed($key)) {
            return false;
        }

        return (bool) config('modules.' . $key, true);
    }

    public static function installed(string $module): bool
    {
        $key = static::key($module);

        if (! $key) {
            return false;
        }

        $definition = static::definitions()[$key];

        return is_dir(base_path('Modules/' . $definition['namespace']))
            && is_file(base_path('Modules/' . $definition['namespace'] . '/' . $definition['namespace'] . 'ServiceProvider.php'));
    }

    public static function settingKey(string $module): ?string
    {
        $key = static::key($module);

        return $key ? 'module_' . $key . '_enabled' : null;
    }

    public static function envKey(string $module): ?string
    {
        $key = static::key($module);

        return $key ? 'CMS_MODULE_' . strtoupper($key) . '_ENABLED' : null;
    }

    /**
     * @return array<string, array{label: string, type: string}>
     */
    public static function settingsFields(): array
    {
        $fields = [];

        foreach (static::definitions() as $key => $definition) {
            $fields[static::settingKey($key)] = [
                'label' => $definition['name'] . ' Module',
                'type' => 'boolean',
            ];
        }

        return $fields;
    }
}
