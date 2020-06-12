<?php

/** @var Factory $factory */

use App\People;
use App\Station;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(People::class, function (Faker $faker) {
    return [
        'creator_id' => User::all()->random()->id,
        'station_id' => $faker->boolean(50) ? Station::all()->random()->id : null,
        'name' => $faker->name,
        'email' => $faker->boolean(50) ? $faker->email : null,
        'telephone_number' => $faker->boolean(50) ? $faker->phoneNumber : null,
        'member_card_number' => $faker->boolean(50) ? $faker->randomNumber(7 ) : null,
    ];
});
