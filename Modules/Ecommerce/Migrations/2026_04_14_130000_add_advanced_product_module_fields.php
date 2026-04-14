<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type', 30)->default('select')->after('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('delivery_rules')->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('delivery_rules');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
