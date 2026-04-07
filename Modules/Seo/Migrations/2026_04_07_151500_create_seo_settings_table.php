<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_type')->default('page');
            $table->string('page_key');
            $table->string('page_label')->nullable();
            $table->string('seo_meta_title')->nullable();
            $table->text('seo_meta_description')->nullable();
            $table->text('seo_meta_keywords')->nullable();
            $table->string('seo_og_image')->nullable();
            $table->string('seo_twitter_card')->default('summary_large_image');
            $table->string('seo_canonical_url')->nullable();
            $table->enum('seo_indexing', ['index', 'noindex'])->default('index');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['page_type', 'page_key']);
            $table->index(['page_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
