<?php

use App\Providers\AppServiceProvider;
use Modules\Backup\BackupServiceProvider;
use Modules\Banner\BannerServiceProvider;
use Modules\Careers\CareersServiceProvider;
use Modules\FormBuilder\FormBuilderServiceProvider;
use Modules\Gallery\GalleryServiceProvider;
use Modules\Email\EmailServiceProvider;
use Modules\Menu\MenuServiceProvider;
use Modules\Page\PageServiceProvider;
use Modules\Seo\SeoServiceProvider;
use Modules\Services\ServicesServiceProvider;
use Modules\Settings\SettingsServiceProvider;

return [
    AppServiceProvider::class,
    BackupServiceProvider::class,
    BannerServiceProvider::class,
    CareersServiceProvider::class,
    FormBuilderServiceProvider::class,
    GalleryServiceProvider::class,
    EmailServiceProvider::class,
    MenuServiceProvider::class,
    PageServiceProvider::class,
    SeoServiceProvider::class,
    ServicesServiceProvider::class,
    SettingsServiceProvider::class,
];
