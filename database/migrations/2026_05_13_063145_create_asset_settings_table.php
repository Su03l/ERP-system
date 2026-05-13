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
        Schema::create('asset_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('asset_code_prefix')->default('AST');
            $table->boolean('depreciation_enabled')->default(true);
            $table->string('default_depreciation_method')->default('straight_line');
            $table->boolean('custody_approval_required')->default(true);
            $table->boolean('asset_return_approval_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index(['company_id', 'depreciation_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_settings');
    }
};
