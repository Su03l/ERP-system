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
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('default_dashboard_date_range')->default('this_month');
            $table->string('kpi_refresh_frequency')->default('daily');
            $table->string('export_language')->default('ar');
            $table->boolean('pdf_export_enabled')->default(true);
            $table->boolean('excel_export_enabled')->default(true);
            $table->json('dashboard_widgets_enabled')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index('export_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_settings');
    }
};
