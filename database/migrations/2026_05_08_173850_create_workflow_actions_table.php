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
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('acted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['company_id', 'action']);
            $table->index(['workflow_instance_id', 'workflow_step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};
