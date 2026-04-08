<?php

namespace Modules\Email;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'email');

        if (is_file(__DIR__ . '/Support/helpers.php')) {
            require_once __DIR__ . '/Support/helpers.php';
        }

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
