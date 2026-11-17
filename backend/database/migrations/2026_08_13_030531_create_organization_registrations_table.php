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
        Schema::create('organization_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_type'); // We'll let validation handle the enum
            $table->string('organization_subdomain')->unique();
            $table->string('organization_logo')->nullable();
            $table->text('organization_description')->nullable();

            $table->string('admin_first_name');
            $table->string('admin_last_name');
            $table->string('admin_email');
            $table->string('admin_password');

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_registrations');
    }
};
