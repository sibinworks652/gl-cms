<?php

use App\Providers\AppServiceProvider;
use Modules\Backup\BackupServiceProvider;
use Modules\ActivityLogs\ActivityLogsServiceProvider;
use Modules\Banner\BannerServiceProvider;
use Modules\Careers\CareersServiceProvider;
use Modules\Faq\FaqServiceProvider;
use Modules\FormBuilder\FormBuilderServiceProvider;
use Modules\Gallery\GalleryServiceProvider;
use Modules\Email\EmailServiceProvider;
use Modules\Menu\MenuServiceProvider;
use Modules\Page\PageServiceProvider;
use Modules\Seo\SeoServiceProvider;
use Modules\Services\ServicesServiceProvider;
use Modules\Settings\SettingsServiceProvider;
use Modules\Team\TeamServiceProvider;
use Modules\Testimonials\TestimonialsServiceProvider;

$moduleToggles = is_file(dirname(__DIR__) . '/config/modules.php')
    ? require dirname(__DIR__) . '/config/modules.php'
    : [];

$providers = [
    AppServiceProvider::class,
    ActivityLogsServiceProvider::class,
    BackupServiceProvider::class,
    BannerServiceProvider::class,
    CareersServiceProvider::class,
    FaqServiceProvider::class,
    FormBuilderServiceProvider::class,
    GalleryServiceProvider::class,
    EmailServiceProvider::class,
    MenuServiceProvider::class,
    ServicesServiceProvider::class,
    TeamServiceProvider::class,
    TestimonialsServiceProvider::class,
    SeoServiceProvider::class,
    SettingsServiceProvider::class,
    PageServiceProvider::class,
];

$providerModules = [
    ActivityLogsServiceProvider::class => 'activity_logs',
    BackupServiceProvider::class => 'backup',
    BannerServiceProvider::class => 'banner',
    CareersServiceProvider::class => 'careers',
    FaqServiceProvider::class => 'faq',
    FormBuilderServiceProvider::class => 'form_builder',
    GalleryServiceProvider::class => 'gallery',
    EmailServiceProvider::class => 'email',
    MenuServiceProvider::class => 'menu',
    PageServiceProvider::class => 'page',
    SeoServiceProvider::class => 'seo',
    ServicesServiceProvider::class => 'services',
    TeamServiceProvider::class => 'team',
    TestimonialsServiceProvider::class => 'testimonials',
];

$providerPath = static function (string $provider): ?string {
    $basePath = dirname(__DIR__);

    if (str_starts_with($provider, 'App\\')) {
        return $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($provider, 4)) . '.php';
    }

    if (str_starts_with($provider, 'Modules\\')) {
        return $basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $provider) . '.php';
    }

    return null;
};

return array_values(array_filter($providers, static function (string $provider) use ($moduleToggles, $providerModules, $providerPath): bool {
    $module = $providerModules[$provider] ?? null;

    if ($module && ! ($moduleToggles[$module] ?? true)) {
        return false;
    }

    $path = $providerPath($provider);

    return $path ? file_exists($path) : true;
}));
