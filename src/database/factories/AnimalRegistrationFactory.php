<?php

/** @var Factory $factory */

use App\Animal;
use App\AnimalRegistration;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(AnimalRegistration::class, function (Faker $faker) {
    $record = [
        'animal_id' => Animal::all()->random()->id,
        'registrator_id' => User::all()->random()->id,
        'club' => $faker->randomElement(['CZKP', 'SOP', 'Other']),
        'registration_number' => $faker->randomNumber(),
        'breeding_available' => $faker->boolean,
    ];

    if ($record['club'] === 'CZKP') {
        $record['type'] = $faker->randomElement(['CZ', 'CZN']);
        $record['year'] = $faker->year;
    }

    if ($record['breeding_available']) {
        $record['breeding_limitation'] = $faker->boolean(50) ? $faker->numberBetween(0, 1) : $faker->text;
    }

    return $record;
});
