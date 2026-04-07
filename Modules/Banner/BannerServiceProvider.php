<?php

namespace Modules\Banner;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'banner');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
