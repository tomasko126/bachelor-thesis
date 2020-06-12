<?php

namespace App\Policies;

use App\Animal;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnimalPolicy
{
    use HandlesAuthorization;

    /**
     * Allow any action for admin
     *
     * @param User $user
     * @return bool
     */
    public function before($user) {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Animal $animal
     * @return mixed
     */
    public function update(User $user, Animal $animal)
    {
        return $this->canModifyAnimal($user, $animal);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Animal $animal
     * @return mixed
     */
    public function delete(User $user, Animal $animal)
    {
        return $this->canModifyAnimal($user, $animal);
    }

    /**
     * Determine whether user can restore the model.
     *
     * @param User $user
     * @param Animal $animal
     * @return bool
     */
    public function restore(User $user, Animal $animal) {
        // Only admin can restore the animal
        return false;
    }

    /**
     * Determine, whether user can export animal
     *
     * @param User $user
     * @param Animal $animal
     * @return bool
     */
    public function canExportAnimal(User $user, Animal $animal) {
        // Allow to download animal export for animals creator and owner
        if ($user->id == $animal->creator_id || ($animal->owner && $user->id == $animal->owner->user_id)) {
            return true;
        }

        return $user->can('download animal summary');
    }

    /**
     * Determine, whether user can update/delete Animal model
     *
     * @param User $user
     * @param Animal $animal
     * @return bool
     */
    private function canModifyAnimal(User $user, Animal $animal) {
        // Do not allow to update/delete animal when it has already been deleted
        if ($animal->trashed()) {
            return false;
        }

        // Check, whether animal has CZKP registrations
        // If yes, user needs permission to modify this animal
        $animalRegistrations = $animal->registrations()->get();
        $isRegisteredUnderCZKP = false;

        foreach ($animalRegistrations as $registration) {
            if ($registration->club === 'CZKP') {
                $isRegisteredUnderCZKP = true;
                break;
            }
        }

        if ($isRegisteredUnderCZKP && $user->cant('edit animals with czkp registration')) {
            return false;
        }

        $animalCreatorId = $animal->creator_id;
        $animalOwner = $animal->owner->user;

        // Allow to modify animal for the one, who created it
        if ($user->id == $animalCreatorId || ($animalOwner && $user->id == $animalOwner->id)) {
            return true;
        }

        return $user->can('edit foreign animals');
    }
}
