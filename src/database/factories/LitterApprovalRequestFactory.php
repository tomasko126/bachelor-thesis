<?php

/** @var Factory $factory */

use App\Litter;
use App\LitterApprovalRequest;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(LitterApprovalRequest::class, function (Faker $faker) {

    $ppandnvlitters = Litter::all()->whereIn('type', ['PP', 'NV']);

    $record = [
        'creator_id' => User::all()->random()->id,
        'registrator_id' => User::all()->random()->id,
        'litter_id' => $ppandnvlitters->random()->id,
        'state' => $faker->randomElement(['Sent', 'Approved', 'Rejected']),
        'registration_date' => $faker->date('m.d.Y'),
        'creator_note' => $faker->realText(),
    ];

    if (!empty($record['registration_date'])) {
        $record['registration_number'] = $faker->randomNumber(8, true);
    }

    if ($record['state'] !== 'Sent') {
        $record['registrator_note'] = $faker->realText();
    }

    return $record;
});
