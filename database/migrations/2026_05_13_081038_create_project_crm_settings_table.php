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
        Schema::create('project_crm_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('project_code_prefix')->default('PRJ');
            $table->string('task_code_prefix')->default('TSK');
            $table->string('default_project_status')->default('draft');
            $table->boolean('project_approval_required')->default(true);
            $table->boolean('task_approval_required')->default(false);
            $table->boolean('time_tracking_enabled')->default(true);
            $table->boolean('billable_time_enabled')->default(false);
            $table->boolean('crm_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index(['company_id', 'crm_enabled']);
            $table->index(['company_id', 'default_project_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_crm_settings');
    }
};
