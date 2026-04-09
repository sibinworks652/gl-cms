<?php

namespace Modules\Careers\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Careers\Models\JobCategory;

class CareersCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Development', 'Marketing', 'Design', 'HR'] as $categoryName) {
            JobCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => $categoryName . ' opportunities',
                    'is_active' => true,
                ]
            );
        }
    }
}
