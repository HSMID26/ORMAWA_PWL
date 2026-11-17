<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'email')) {
                $table->string('email')->nullable()->after('subdomain');
            }
            if (!Schema::hasColumn('organizations', 'telepon')) {
                $table->string('telepon')->nullable()->after('email');
            }
            if (!Schema::hasColumn('organizations', 'alamat')) {
                $table->text('alamat')->nullable()->after('telepon');
            }
            if (!Schema::hasColumn('organizations', 'media_sosial')) {
                $table->json('media_sosial')->nullable()->after('alamat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $cols = [];
            foreach (['email', 'telepon', 'alamat', 'media_sosial'] as $col) {
                if (Schema::hasColumn('organizations', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
