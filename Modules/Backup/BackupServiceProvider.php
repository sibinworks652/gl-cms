<?php

namespace Modules\Backup;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Backup\Commands\CreateBackupCommand;

class BackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'backup');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateBackupCommand::class,
            ]);
        }
    }
}
