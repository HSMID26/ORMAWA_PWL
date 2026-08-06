<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Multi-tenancy & User Relationship
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Content Details
            $table->string('judul');
            $table->string('slug')->unique(); // Untuk URL ramah SEO (misal: /posts/workshop-multimedia)
            $table->longText('konten'); // <--- Di sini tempat menyimpan HTML dari TipTap
            $table->string('cover_image')->nullable(); // Gambar sampul/thumbnail artikel
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
