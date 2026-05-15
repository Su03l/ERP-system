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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('widget_key');
            $table->string('module');
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('type');
            $table->string('resolver');
            $table->string('required_permission')->nullable();
            $table->string('default_size')->default('medium');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'widget_key']);
            $table->index(['module', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
