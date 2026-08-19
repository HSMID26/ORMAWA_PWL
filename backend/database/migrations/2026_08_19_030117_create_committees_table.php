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
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Jika pengurus punya akun login
            $table->string('name');
            $table->string('position'); // Jabatan: Ketua, Sekretaris, Anggota Divisi IT, dll.
            $table->string('department')->nullable(); // Divisi/Departemen (opsional)
            $table->string('period'); // Periode: 2025/2026
            $table->string('photo')->nullable(); // Foto profil pengurus
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
