<?php

namespace Modules\Gallery;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GalleryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'gallery');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
        Route::middleware('api')
            ->group(__DIR__ . '/Routes/api.php');
    }
}
