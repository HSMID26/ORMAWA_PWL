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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['HMPS', 'UKM', 'BEM', 'Senat', 'Lainnya']);
            $table->string('subdomain')->unique(); // contoh: hmif.kampus.ac.id
            $table->string('logo')->nullable();
            $table->string('warna_tema')->default('#000000');
            $table->json('modul_aktif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
