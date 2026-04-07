<?php

namespace Modules\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'services');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
