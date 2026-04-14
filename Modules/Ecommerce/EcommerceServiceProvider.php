<?php

namespace Modules\Ecommerce;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EcommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'ecommerce');

        foreach (glob(__DIR__ . '/Support/*.php') as $file) {
            require_once $file;
        }

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');

        Route::middleware('api')
            ->group(__DIR__ . '/Routes/api.php');
    }
}
