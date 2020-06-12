<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AddRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'add foreign owner to litter']);
        Permission::create(['name' => 'add foreign owner to animal']);
        Permission::create(['name' => 'edit approved litters']);
        Permission::create(['name' => 'edit animals with czkp registration']);
        Permission::create(['name' => 'edit foreign litters']);
        Permission::create(['name' => 'answer to litter requests']);
        Permission::create(['name' => 'see litter requests']);
        Permission::create(['name' => 'edit foreign animals']);
        Permission::create(['name' => 'see not approved litters']);
        Permission::create(['name' => 'modify registration to foreign animal']);
        Permission::create(['name' => 'modify czkp registration']);
        Permission::create(['name' => 'add note to foreign animals']);
        Permission::create(['name' => 'add note to foreign litters']);
        Permission::create(['name' => 'download animal summary']);

        // create roles and assign created permissions

        // this can be done as separate statements
        Role::create(['name' => 'litters requests approver'])
            ->givePermissionTo(
                [
                    'answer to litter requests',
                    'see litter requests',
                    'edit foreign animals',
                    'edit foreign litters',
                    'see not approved litters',
                    'add note to foreign litters',
                    'download animal summary',
                ]
            );

        // or may be done by chaining
        Role::create(['name' => 'registrator'])
            ->givePermissionTo(
                [
                    'modify registration to foreign animal',
                    'modify czkp registration',
                    'add note to foreign animals',
                ]);

        Role::create(['name' => 'user']);

        Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
