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
        Schema::create('migration_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('import_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('uploaded_file_path');
            $table->string('target_entity');
            $table->string('module_key');
            $table->json('column_mapping')->nullable();
            $table->json('validation_result')->nullable();
            $table->string('dry_run_status')->default('pending');
            $table->string('final_import_status')->default('pending');
            $table->timestamps();

            $table->index(['company_id', 'module_key']);
            $table->index(['company_id', 'target_entity']);
            $table->index(['company_id', 'dry_run_status', 'final_import_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_sessions');
    }
};
