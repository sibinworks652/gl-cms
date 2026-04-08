<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'use_header')) {
                $table->boolean('use_header')->default(true)->after('variables');
            }

            if (! Schema::hasColumn('email_templates', 'use_footer')) {
                $table->boolean('use_footer')->default(true)->after('use_header');
            }

            if (! Schema::hasColumn('email_templates', 'use_signature')) {
                $table->boolean('use_signature')->default(true)->after('use_footer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            foreach (['use_signature', 'use_footer', 'use_header'] as $column) {
                if (Schema::hasColumn('email_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
