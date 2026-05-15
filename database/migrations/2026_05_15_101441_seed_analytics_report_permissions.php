<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $permissions = [
        'analytics.view' => 'View analytics',
        'analytics.export' => 'Export analytics',
        'dashboard_widgets.view' => 'View dashboard widgets',
        'dashboard_widgets.manage' => 'Manage dashboard widgets',
        'reports.view' => 'View reports',
        'reports.run' => 'Run reports',
        'reports.export' => 'Export reports',
        'kpi.hr.view' => 'View HR KPIs',
        'kpi.attendance.view' => 'View attendance KPIs',
        'kpi.payroll.view' => 'View payroll KPIs',
        'kpi.finance.view' => 'View finance KPIs',
        'kpi.projects.view' => 'View project KPIs',
        'kpi.saas.view' => 'View SaaS KPIs',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $key => $name) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                [
                    'name' => $name,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('key', array_keys($this->permissions))->delete();
    }
};
