<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_number', 80);
            $table->string('type', 20);
            $table->string('sender');
            $table->string('recipient');
            $table->string('subject');
            $table->date('letter_date');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime_type', 100)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'reference_number']);
            $table->index(['organization_id', 'status', 'letter_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondences');
    }
};
