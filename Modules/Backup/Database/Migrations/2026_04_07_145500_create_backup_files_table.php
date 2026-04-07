<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('filename')->unique();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('google_uploaded')->default(false);
            $table->string('google_path')->nullable();
            $table->text('google_error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['disk', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_files');
    }
};
