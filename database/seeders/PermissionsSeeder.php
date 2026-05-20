<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a Super Admin role
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'key' => 'super-admin',
            'description' => 'Full access to all system features.',
            'company_id' => 1, // Assuming company with ID 1 is the main company
        ]);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to the Super Admin role
        $superAdminRole->permissions()->sync($permissions->pluck('id'));

        // Find the admin user and assign the Super Admin role
        $adminUser = \App\Models\User::where('email', 'admin@nawwat.com')->first();
        if ($adminUser) {
            $adminUser->roles()->attach($superAdminRole);
        }
    }
}
