<?php

/** @var Factory $factory */

use App\Animal;
use App\Litter;
use App\Note;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Note::class, function (Faker $faker) {

    $result = [
        'creator_id' => User::all()->random()->id,
        'note' => $faker->text,
        'public' => $faker->randomElement([true, false]),
        'category' => $faker->randomElement(['general', 'warning', 'alert']),
    ];

    $random = $faker->randomElements([1, 2]);

    switch ($random) {
        case 1:
            $result['animal_id'] = Animal::all()->random()->id;
            $result['litter_id'] = null;
            break;
        case 2:
            $result['animal_id'] = null;
            $result['litter_id'] = Litter::all()->random()->id;
            break;
    }

    return $result;
});
