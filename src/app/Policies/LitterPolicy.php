<?php

namespace App\Policies;

use App\Litter;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LitterPolicy
{
    use HandlesAuthorization;

    private const LITTER_APPROVAL_REQUEST_APPROVED = 'Approved';

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
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Litter $litter
     * @return mixed
     */
    public function view(User $user, Litter $litter)
    {
        // User is either creator or owner of this litter
        if ($user->id == $litter->creator_id || $user->id == $litter->owner->user_id) {
            return true;
        }

        $latestLitterRequest = $litter->latestApprovalRequest();

        // There is no litter approval request tied to this litter
        if (!$latestLitterRequest || !$latestLitterRequest->exists()) {
            if ($litter->type !== 'VP') {
                return $user->can('see not approved litters');
            }
            return true;
        }

        $latestLitterRequest = $latestLitterRequest->first();

        // If state of the latest litter request is approved,
        // allow this litter to be seen by everyone
        if ($latestLitterRequest->state === self::LITTER_APPROVAL_REQUEST_APPROVED) {
            return true;
        }

        // Otherwise user must have a permission to see this litter
        return $user->can('see not approved litters');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Litter $litter
     * @return mixed
     */
    public function update(User $user, Litter $litter)
    {
        return $this->canModifyLitter($user, $litter);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Litter $litter
     * @return mixed
     */
    public function delete(User $user, Litter $litter)
    {
        return $this->canModifyLitter($user, $litter);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Litter $litter
     * @return bool
     */
    public function restore(User $user, Litter $litter) {
        // Only admin can restore litters
        return false;
    }

    /**
     * Determine, whether user can see litter's approval requests
     *
     * @param User $user
     * @param Litter $litter
     * @return bool
     */
    public function viewLitterRequests(User $user, Litter $litter) {
        if ($user->id == $litter->owner->user_id || $user->id == $litter->creator_id) {
            return true;
        }

        return $user->can('see litter requests');
    }

    /**
     * Determine, whether user can edit/delete litter
     *
     * @param User $user
     * @param Litter $litter
     * @return bool
     */
    private function canModifyLitter(User $user, Litter $litter) {
        // User needs permission in order to edit/delete already approved litter
        $latestApprovalRequest = $litter->latestApprovalRequest();

        if ($latestApprovalRequest && $latestApprovalRequest->exists()) {

            $latestApprovalRequest = $latestApprovalRequest->first();

            if ($latestApprovalRequest->state === 'Approved') {
                return $user->can('edit approved litters');
            }
        }

        if ($user->id == $litter->owner->user_id || $user->id == $litter->creator_id) {
            return true;
        }

        return $user->can('edit foreign litters');
    }
}
