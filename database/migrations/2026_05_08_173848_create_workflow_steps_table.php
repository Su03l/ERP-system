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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('approver_type');
            $table->string('approver_value');
            $table->unsignedInteger('order');
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->unique(['workflow_id', 'order']);
            $table->index(['workflow_id', 'approver_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
