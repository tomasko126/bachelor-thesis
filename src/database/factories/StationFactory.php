<?php

/** @var Factory $factory */

use App\Station;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Station::class, function (Faker $faker) {
    return [
        'creator_id' => User::all()->random()->id,
        'name' => $faker->unique()->company
    ];
});
