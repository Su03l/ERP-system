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
        Schema::create('document_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('default_expiry_reminder_days')->default(30);
            $table->json('allowed_file_types')->nullable();
            $table->unsignedInteger('max_file_size')->nullable();
            $table->boolean('document_approval_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index(['company_id', 'document_approval_required']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_settings');
    }
};
