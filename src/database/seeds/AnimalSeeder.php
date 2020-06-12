<?php

use App\Animal;
use Faker\Factory;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */
    public function run()
    {
        $faker = Factory::create();

        factory(Animal::class, 50)->state('Female')->create();
        factory(Animal::class, 50)->state('Male')->create();

        $animals = Animal::all();
        $females = Animal::query()->where('sex', 'Female')->get();
        $males = Animal::query()->where('sex', 'Male')->get();

        foreach ($animals as $animal) {
            if ($faker->boolean(50)) {
                $animal->mother_id = $females->random()->id;
                $animal->father_id = $males->random()->id;

                $animal->saveOrFail();
            }
        }
    }
}
