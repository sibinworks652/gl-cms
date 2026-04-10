<?php

namespace Modules\Team;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TeamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'team');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
