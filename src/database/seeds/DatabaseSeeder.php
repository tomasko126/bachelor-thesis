<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            StationSeeder::class,
            PeopleSeeder::class,
            AnimalSeeder::class,
            AnimalRegistrationSeeder::class,
            LitterSeeder::class,
            LitterApprovalRequestSeeder::class,
            NoteSeeder::class,
        ]);
    }
}
