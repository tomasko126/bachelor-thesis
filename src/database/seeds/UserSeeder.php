<?php

use App\People;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::query()->delete();

        $password = Hash::make('password');

        $admin = new User();
        $admin->name = 'Admin';
        $admin->email = 'tarotoma+admin@fit.cvut.cz';
        $admin->password = $password;
        $admin->assignRole('admin');
        $admin->saveOrFail();

        $adminPeople = new People();
        $adminPeople->name = $admin->name;
        $adminPeople->email = $admin->email;
        $adminPeople->creator_id = $admin->id;
        $adminPeople->user_id = $admin->id;
        $adminPeople->saveOrFail();

        $registrator = new User();
        $registrator->name = 'Registrator';
        $registrator->email = 'tarotoma+registrator@fit.cvut.cz';
        $registrator->password = $password;
        $registrator->assignRole('registrator');
        $registrator->saveOrFail();

        $registratorPeople = new People();
        $registratorPeople->name = $registrator->name;
        $registratorPeople->email = $registrator->email;
        $registratorPeople->creator_id = $admin->id;
        $registratorPeople->user_id = $registrator->id;
        $registratorPeople->saveOrFail();

        $approver = new User();
        $approver->name = 'Approver';
        $approver->email = 'tarotoma+approver@fit.cvut.cz';
        $approver->password = $password;
        $approver->assignRole('litters requests approver');
        $approver->saveOrFail();

        $approverPeople = new People();
        $approverPeople->name = $approver->name;
        $approverPeople->email = $approver->email;
        $approverPeople->creator_id = $admin->id;
        $approverPeople->user_id = $approver->id;
        $approverPeople->saveOrFail();

        $user = new User();
        $user->name = 'User';
        $user->email = 'tarotoma+user@fit.cvut.cz';
        $user->password = $password;
        $user->assignRole('user');
        $user->saveOrFail();

        $userPeople = new People();
        $userPeople->name = $user->name;
        $userPeople->email = $user->email;
        $userPeople->creator_id = $admin->id;
        $userPeople->user_id = $user->id;
        $userPeople->saveOrFail();

        factory(User::class, 3)->create();
    }
}
