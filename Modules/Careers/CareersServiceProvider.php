<?php

namespace Modules\Careers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CareersServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'careers');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
