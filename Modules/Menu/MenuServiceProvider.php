<?php

namespace Modules\Menu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Menu\Models\Menu;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'menu');

        Route::middleware('web')
            ->group(__DIR__ . '/Routes/web.php');

        Route::middleware('api')
            ->group(__DIR__ . '/Routes/api.php');

        View::composer('*', function ($view) {

            static $menus = null;

            if ($menus !== null) {
                return $view->with('dynamicMenus', $menus);
            }

            // Avoid errors during fresh install
            if (!Schema::hasTable('menus') || !Schema::hasTable('menu_items')) {
                return $view->with('dynamicMenus', collect());
            }

            $menus = Menu::query()
                ->active()
                ->with(['rootItems.childrenRecursive'])
                ->get()
                ->keyBy('location');

            return $view->with('dynamicMenus', $menus);
        });
    }
}
