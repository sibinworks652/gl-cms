<?php

namespace Modules\ActivityLogs;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ActivityLogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once __DIR__ . '/Support/helpers.php';
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'activity-logs');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
