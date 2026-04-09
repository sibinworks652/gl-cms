<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location');
            $table->string('job_type');
            $table->string('experience');
            $table->string('salary')->nullable();
            $table->unsignedInteger('vacancies')->default(1);
            $table->text('short_description');
            $table->longText('description');
            $table->longText('skills')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->longText('benefits')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('active');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_jobs');
    }
};
