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
        Schema::create('usage_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('users_count')->default(0);
            $table->unsignedInteger('employees_count')->default(0);
            $table->unsignedBigInteger('storage_usage_mb')->default(0);
            $table->unsignedInteger('active_modules_count')->default(0);
            $table->unsignedBigInteger('api_requests_count')->default(0);
            $table->unsignedBigInteger('exports_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            $table->index(['company_id', 'captured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_snapshots');
    }
};
