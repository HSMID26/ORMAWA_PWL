<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->dropUnique('categories_slug_unique');
            } catch (\Throwable $e) {
                try {
                    $table->dropUnique(['slug']);
                } catch (\Throwable $e2) {}
            }

            try {
                $table->unique(['organization_id', 'slug']);
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->dropUnique(['organization_id', 'slug']);
            } catch (\Throwable $e) {}

            try {
                $table->unique('slug');
            } catch (\Throwable $e) {}
        });
    }
};
