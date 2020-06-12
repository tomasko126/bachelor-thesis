<?php

namespace App\ImportModels;

use App\People;
use App\User;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractImport extends Model
{
    // Create new system user with wordpress id
    // New person will get created as well, if user does not exist
    protected static function processWPUser($wordpressId, $entityToReturn) {
        if (empty($wordpressId)) {
            return null;
        }

        $user = User::with('people')->where('wordpress_id', $wordpressId)->first();
        $person = null;

        if (!$user || !$user->exists()) {
            $faker = resolve(FakerGenerator::class);

            $userName = $faker->unique()->name;
            $userEmail = $faker->unique()->email;

            $user = new User();
            $user->wordpress_id = $wordpressId;
            $user->name = $userName;
            $user->email = $userEmail;
            $user->password = '';

            $user->assignRole('user');

            $user->saveOrFail();

            $user->refresh();

            People::createPerson(
                [
                    'user_id' => $user->id,
                    'name' => $userName,
                ],
                $person
            );
        }

        if ($entityToReturn === People::class) {
            if (!$person || !$user->people()->exists()) {
                $person = People::query()->where('name', $user->name);

                if (!$person->exists()) {
                    People::createPerson(
                        [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ],
                        $person
                    );
                } else {
                    $person = $person->first();
                }
            }

            return $person->id;
        }

        if ($entityToReturn === User::class) {
            return $user->id;
        }
    }
}
