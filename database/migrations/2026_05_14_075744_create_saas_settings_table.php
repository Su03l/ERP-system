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
        Schema::create('saas_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('default_trial_days')->default(14);
            $table->string('default_currency', 3)->default('SAR');
            $table->boolean('billing_enabled')->default(false);
            $table->boolean('marketplace_enabled')->default(false);
            $table->string('invoice_numbering_prefix')->default('SAAS-INV');
            $table->unsignedSmallInteger('subscription_grace_period_days')->default(7);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saas_settings');
    }
};
