<?php

/** @var Factory $factory */

use App\Animal;
use App\People;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Animal::class, function (Faker $faker) {
    return [
        'creator_id' => User::all()->random()->id,
        'breeder_id' => People::all()->random()->id,
        'owner_id' => People::all()->random()->id,
        'name' => $faker->name,
        'nickname' => $faker->boolean(50) ? $faker->userName : null,
        'sex' => $faker->randomElement(['Male', 'Female']),
        'birthdate' => $faker->boolean(50) ? $faker->date('d.m.Y') : null,
        'eyes_color' => $faker->randomElement(['Black', 'Dark Ruby', 'Ruby', 'Red', 'Pink', 'Odd eyed']),
        'ear_type' => $faker->randomElement(['Standart', 'Standart Dg', 'Dumbo']),
        'fur_color' => $faker->words(1, true),
        'fur_type' => $faker->words(1, true),
        'markings' => $faker->words(2, true),
        'death_date' => $faker->boolean(50) ? $faker->date('d.m.Y') : null,
        'death_reason' => $faker->boolean(50) ? $faker->text : null,
    ];
});

$factory->state(Animal::class, 'Female', [
    'sex' => 'Female',
]);

$factory->state(Animal::class, 'Male', [
    'sex' => 'Male',
]);
