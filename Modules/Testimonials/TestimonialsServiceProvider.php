<?php

namespace Modules\Testimonials;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TestimonialsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'testimonials');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');
    }
}
