<?php

namespace Modules\Page;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'page');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
