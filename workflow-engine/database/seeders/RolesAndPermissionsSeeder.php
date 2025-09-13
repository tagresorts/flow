<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'create workflows']);
        Permission::create(['name' => 'edit workflows']);
        Permission::create(['name' => 'delete workflows']);
        Permission::create(['name' => 'view workflows']);

        Permission::create(['name' => 'create requests']);
        Permission::create(['name' => 'view own requests']);
        Permission::create(['name' => 'view all requests']);

        Permission::create(['name' => 'approve requests']);
        Permission::create(['name' => 'reject requests']);

        // create roles and assign created permissions

        $requesterRole = Role::create(['name' => 'Requester']);
        $requesterRole->givePermissionTo(['create requests', 'view own requests']);

        $approverRole = Role::create(['name' => 'Approver']);
        $approverRole->givePermissionTo(['view all requests', 'approve requests', 'reject requests']);

        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
