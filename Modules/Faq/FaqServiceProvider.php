<?php

namespace Modules\Faq;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FaqServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'faq');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
