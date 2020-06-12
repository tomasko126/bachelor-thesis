<?php

namespace App\Policies;

use App\Note;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
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
     * Determine whether the user can create models.
     *
     * @param User $user
     * @param Note $note
     * @return boolean
     */
    public function create(User $user, Note $note)
    {
        if (!empty($note->animal_id)) {
            // Allow adding animal notes to user, who created the animal
            if ($note->animal->creator_id == $user->id) {
                return true;
            }

            // Allow adding animal notes to user, who is his owner
            $isAnimalOwnerSystemUser = $note->animal->owner->user ?? null;

            if (!$isAnimalOwnerSystemUser) {
                // Animal has different owner than system user,
                // do not let him to create new note
                return $user->can('add note to foreign animals');
            }

            $animalOwnerId = $isAnimalOwnerSystemUser->id;

            if ($user->id == $animalOwnerId) {
                return true;
            }

            return $user->can('add note to foreign animals');
        }

        if (!empty($note->litter_id)) {
            // Allow adding litter notes to user, who created the litter
            if ($note->litter->creator_id == $user->id) {
                return true;
            }

            // Allow adding litter notes to user, who is his owner
            if ($note->litter->owner->user_id == $user->id) {
                return true;
            }

            return $user->can('add note to foreign litters');
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Note $note
     * @return mixed
     */
    public function view(User $user, Note $note)
    {
        // User can see notes created by himself
        if ($user->id == $note->creator_id) {
            return true;
        }

        // User can see public notes
        if ($note->public) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Note $note
     * @return mixed
     */
    public function update(User $user, Note $note)
    {
        // Note can be updated only by its creator
        return $user->id == $note->creator_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Note $note
     * @return mixed
     */
    public function delete(User $user, Note $note)
    {
        // Note can be deleted only by its creator
        return $user->id == $note->creator_id;
    }
}
