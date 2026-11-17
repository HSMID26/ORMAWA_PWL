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
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('activities', 'start_time')) {
                $table->dateTime('start_time')->nullable();
            }
            if (!Schema::hasColumn('activities', 'end_time')) {
                $table->dateTime('end_time')->nullable();
            }
            if (!Schema::hasColumn('activities', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('activities', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop columns safely
            $cols = ['location', 'start_time', 'end_time', 'title', 'description'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('activities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
