<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('session_timeout_minutes')->default(120);
            $table->json('password_policy')->nullable();
            $table->boolean('two_factor_authentication_enabled')->default(false);
            $table->json('allowed_login_ips')->nullable();
            $table->unsignedInteger('audit_retention_days')->default(365);
            $table->boolean('export_approval_required')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
