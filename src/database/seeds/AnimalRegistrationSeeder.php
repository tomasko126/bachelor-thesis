<?php

use App\AnimalRegistration;
use Illuminate\Database\Seeder;

class AnimalRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(AnimalRegistration::class, 40)->create();
    }
}
