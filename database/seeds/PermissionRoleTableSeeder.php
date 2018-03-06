<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        $adminRole      = Role::where('name', 'admin')->firstOrFail();
        $allPermissions = Permission::all();

        $adminRole->permissions()->sync(
            $allPermissions->pluck('id')->all()
        );

        $userRole = Role::where('name', 'user')->firstOrFail();
        $addTasks = Permission::where('key', 'add_tasks')->firstOrFail();

        $userRole->permissions()->sync([$addTasks->id]);

    }
}
