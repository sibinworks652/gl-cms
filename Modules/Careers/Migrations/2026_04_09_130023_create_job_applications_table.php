<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('career_jobs')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('resume_path');
            $table->string('resume_original_name')->nullable();
            $table->longText('cover_letter')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
