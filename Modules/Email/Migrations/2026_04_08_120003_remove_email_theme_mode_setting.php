<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'email')
            ->where('key', 'email_theme_mode')
            ->delete();
    }

    public function down(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'email_theme_mode'],
            [
                'group' => 'email',
                'value' => 'light',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
};
