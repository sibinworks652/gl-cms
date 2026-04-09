<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'to_emails')) {
                $table->json('to_emails')->nullable()->after('variables');
            }

            if (! Schema::hasColumn('email_templates', 'cc_emails')) {
                $table->json('cc_emails')->nullable()->after('to_emails');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            foreach (['cc_emails', 'to_emails'] as $column) {
                if (Schema::hasColumn('email_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
