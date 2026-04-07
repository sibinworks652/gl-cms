<?php

namespace Modules\Seo;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'seo');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
