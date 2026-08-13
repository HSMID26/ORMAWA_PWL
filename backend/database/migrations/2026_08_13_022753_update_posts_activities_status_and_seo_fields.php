<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add SEO fields to posts
        Schema::table('posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('konten');
            $table->string('meta_title')->nullable()->after('status');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->timestamp('published_at')->nullable()->after('meta_description');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });
        
        // Add published_at to activities
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('status');
        });
        
        Schema::table('activities', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'meta_title', 'meta_description', 'published_at']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
