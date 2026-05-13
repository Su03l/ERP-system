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
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('workflow_instance_id')->nullable()->after('progress_percentage')->constrained('workflow_instances')->nullOnDelete();
            $table->index(['company_id', 'workflow_instance_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'workflow_instance_id']);
            $table->dropConstrainedForeignId('workflow_instance_id');
        });
    }
};
