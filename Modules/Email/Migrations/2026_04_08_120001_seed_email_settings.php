<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $settings = [
        'email_logo' => null,
        'email_header' => '<h2 style="margin:0;">Hello from {site_name}</h2>',
        'email_footer' => '<p style="margin:0;">You are receiving this email from {site_name}.</p>',
        'email_signature' => '<p style="margin:0;">Regards,<br>{site_name}</p>',
        'email_theme_color' => 'var(--bs-primary)',
        'email_text_color' => '#111827',
    ];

    public function up(): void
    {
        foreach ($this->settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'group' => 'email',
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'email')
            ->whereIn('key', array_keys($this->settings))
            ->delete();
    }
};
