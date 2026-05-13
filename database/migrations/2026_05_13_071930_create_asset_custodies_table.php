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
        Schema::create('asset_custodies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('return_received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes_ar')->nullable();
            $table->text('notes_en')->nullable();
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'asset_id']);
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_custodies');
    }
};
