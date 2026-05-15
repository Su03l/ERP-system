<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $permissions = [
        'security_settings.view' => 'View security settings',
        'security_settings.update' => 'Update security settings',
        'audit_logs.view' => 'View audit logs',
        'audit_logs.export' => 'Export audit logs',
        'user_sessions.view' => 'View user sessions',
        'user_sessions.revoke' => 'Revoke user sessions',
        'api_tokens.view' => 'View API tokens',
        'api_tokens.create' => 'Create API tokens',
        'api_tokens.revoke' => 'Revoke API tokens',
        'webhooks.view' => 'View webhooks',
        'webhooks.create' => 'Create webhooks',
        'webhooks.update' => 'Update webhooks',
        'webhooks.delete' => 'Delete webhooks',
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
