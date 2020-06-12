<?php

namespace App\Policies;

use App\Animal;
use App\AnimalRegistration;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnimalRegistrationPolicy
{
    use HandlesAuthorization;

    private const ANIMAL_REGISTRATION_CREATE = 'create';
    private const ANIMAL_REGISTRATION_VIEW = 'view';
    private const ANIMAL_REGISTRATION_UPDATE = 'update';
    private const ANIMAL_REGISTRATION_DELETE = 'delete';

    /**
     * Allow any action for admin
     *
     * @param User $user
     * @return bool
     */
    public function before($user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param AnimalRegistration $animalRegistration
     * @return boolean
     */
    public function create(User $user, AnimalRegistration $animalRegistration)
    {
        return $this->canModifyAnimalRegistration($user, $animalRegistration, self::ANIMAL_REGISTRATION_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AnimalRegistration $animalRegistration
     * @return mixed
     */
    public function update(User $user, AnimalRegistration $animalRegistration)
    {
        return $this->canModifyAnimalRegistration($user, $animalRegistration, self::ANIMAL_REGISTRATION_UPDATE);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AnimalRegistration $animalRegistration
     * @return mixed
     */
    public function delete(User $user, AnimalRegistration $animalRegistration)
    {
        return $this->canModifyAnimalRegistration($user, $animalRegistration, self::ANIMAL_REGISTRATION_DELETE);
    }

    /**
     * Determine, whether user can add/update given Animal Registration model
     *
     * @param User $user
     * @param AnimalRegistration $animalRegistration
     * @param string $state
     * @return bool
     */
    private function canModifyAnimalRegistration(User $user, AnimalRegistration $animalRegistration, string $state) {
        $registration = $animalRegistration->toArray();

        // Do not allow to add/update/delete CZKP registration for users without the permission
        if ($registration['club'] === 'CZKP') {
            if (!$user->can('modify czkp registration')) {
                return false;
            }
        }

        if ($state !== self::ANIMAL_REGISTRATION_DELETE) {
            // User cannot add/update club to the one which has already been taken
            if ($registration['club'] === 'SOP' || $registration['club'] === 'CZKP') {
                $registrationsWithClub = AnimalRegistration::query()->where('animal_id', $registration['animal_id'])->where('club', $registration['club'])->count();

                if ($registrationsWithClub > 0) {
                    return false;
                }
            }
        }

        // Do not allow to add/update/delete animal registration of foreign user without permission
        $userAnimals = Animal::getAnimalsOfUser($user->id)->all();

        $addingRegistrationToForeignAnimal = true;
        foreach ($userAnimals as $animal) {
            if ($animal->id == $registration['animal_id']) {
                $addingRegistrationToForeignAnimal = false;
                break;
            }
        }

        if ($addingRegistrationToForeignAnimal && !$user->can('modify registration to foreign animal')) {
            return false;
        }

        return true;
    }
}
