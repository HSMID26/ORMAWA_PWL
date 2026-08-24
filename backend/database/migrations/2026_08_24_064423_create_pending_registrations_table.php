<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            // Data Ormawa
            $table->string('nama_ormawa');
            $table->enum('jenis_ormawa', ['HMPS', 'UKM', 'BEM', 'Senat', 'Lainnya']);
            $table->string('subdomain')->unique();
            
            // Data Admin Ormawa
            $table->string('admin_name');
            $table->string('admin_email')->unique();
            $table->string('admin_password');
            
            // Status Persetujuan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};