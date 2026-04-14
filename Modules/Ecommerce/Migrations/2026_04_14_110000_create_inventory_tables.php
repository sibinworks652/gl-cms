<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('sku')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity');
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->timestamp('last_restock_at')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'product_variant_id'], 'inventory_product_variant_unique');
            $table->index('sku');
            $table->index(['quantity', 'low_stock_threshold'], 'low_stock_check');
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type');
            $table->integer('quantity_change');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index('type');
            $table->index('created_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_inventory')->default(true)->after('stock');
            $table->integer('low_stock_threshold')->default(10)->after('track_inventory');
            $table->boolean('allow_backorder')->default(false)->after('low_stock_threshold');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('track_inventory')->default(true)->after('stock');
            $table->integer('low_stock_threshold')->default(10)->after('track_inventory');
            $table->boolean('allow_backorder')->default(false)->after('low_stock_threshold');
            $table->text('options')->nullable()->after('allow_backorder');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['options', 'allow_backorder', 'low_stock_threshold', 'track_inventory']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['allow_backorder', 'low_stock_threshold', 'track_inventory']);
        });

        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventories');
    }
};