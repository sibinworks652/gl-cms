<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('name');
            $table->string('value')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id'], 'product_attribute_unique');
        });

        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->constrained('attribute_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_option_id'], 'product_attr_option_unique');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('general');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'tag_id'], 'product_tag_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('sale_price');
            $table->decimal('shipping_weight', 10, 2)->nullable()->after('tax_percentage');
            $table->decimal('shipping_cost', 12, 2)->nullable()->after('shipping_weight');
            $table->boolean('is_featured')->default(false)->change();
            $table->boolean('status')->default(true)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('price');
            $table->decimal('shipping_weight', 10, 2)->nullable()->after('tax_percentage');
            $table->decimal('shipping_cost', 12, 2)->nullable()->after('shipping_weight');
            $table->decimal('length', 8, 2)->nullable()->after('shipping_cost');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height', 'shipping_cost', 'shipping_weight', 'tax_percentage']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'shipping_cost', 'shipping_weight', 'tax_percentage']);
        });

        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
    }
};