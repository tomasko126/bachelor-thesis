<?php

/** @var Factory $factory */

use App\Animal;
use App\Litter;
use App\People;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Litter::class, function (Faker $faker) {
    $mothers = Animal::query()->where('sex', 'Female')->get();
    $fathers = Animal::query()->where('sex', 'Male')->get();

    return [
        'creator_id' => User::all()->random()->id,
        'owner_id' => People::all()->random()->id,
        'father_id' => $fathers->isEmpty() ? null : $fathers->random()->id,
        'mother_id' => $mothers->isEmpty() ? null : $mothers->random()->id,
        'birthdate' => $faker->date('d.m.Y'),
        'label' => $faker->word,
        'line' => $faker->word,
        'genetic_information' => $faker->words(5, true),
        'babies_born' => $faker->randomNumber(2),
        'babies_reared' => $faker->randomNumber(2),
        'reared_boys' => $faker->randomNumber(1),
        'reared_girls' => $faker->randomNumber(1),
        'for_breeding' => $faker->randomNumber(1),
        'for_petting' => $faker->randomNumber(1),
        'type' => $faker->randomElement(['VP', 'PP', 'NV']),
        'breeder_name' => $faker->name,
        'breeder_contact' => $faker->email,
    ];
});
