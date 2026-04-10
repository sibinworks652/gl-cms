<?php

namespace Database\Seeders;

use Database\Seeders\AdminRolePermissionSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminRolePermissionSeeder::class,
        ]);
        $this->runModuleSeeders();

    }
    private function runModuleSeeders()
    {
        foreach (glob(base_path('Modules/*/Seeders/*Seeder.php')) as $file) {

            require_once $file;

            $class = $this->resolveSeederClass($file);

            if (class_exists($class)) {
                $this->call($class);
            } else {
                dump("NOT FOUND: " . $class);
            }
        }
    }
   private function resolveSeederClass($file)
    {
        $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

        $base = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, base_path()) . DIRECTORY_SEPARATOR;

        return str_replace(
            [$base, DIRECTORY_SEPARATOR, '.php'],
            ['', '\\', ''],
            $file
        );
    }
}
