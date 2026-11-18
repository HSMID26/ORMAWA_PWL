<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('responsible_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('objective');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedInteger('target_participants')->default(0);
            $table->decimal('estimated_budget', 15, 2)->default(0);
            $table->string('status', 20)->default('planned');
            $table->timestamps();

            $table->index(['organization_id', 'period_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_programs');
    }
};
