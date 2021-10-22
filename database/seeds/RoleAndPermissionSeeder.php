<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $expert = Role::create(['name' => 'expert']);
        $master = Role::create(['name' => 'master']);
        $administrator = Role::create(['name' => 'administrator']);

        Permission::create(['name' => 'create questions'])
            ->assignRole($expert)
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'edit questions'])
            ->assignRole($expert)
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'review questions'])
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'create answers'])
            ->assignRole($expert)
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'edit answers'])
            ->assignRole($expert)
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'review answers'])
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'edit pending questions'])
            ->assignRole($expert)
            ->assignRole($master)
            ->assignRole($administrator);
        Permission::create(['name' => 'review pending questions'])
            ->assignRole($master)
            ->assignRole($administrator);
    }
}
