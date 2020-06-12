<?php

namespace App\Policies;

use App\LitterApprovalRequest;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LitterApprovalRequestPolicy
{
    use HandlesAuthorization;

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
     * @param LitterApprovalRequest $litterApprovalRequest
     * @return mixed
     */
    public function create(User $user, LitterApprovalRequest $litterApprovalRequest)
    {
        $litter = $litterApprovalRequest->litter;

        // Check, whether litter contains all data we need
        if (!$this->hasRequiredData($litter)) {
            return false;
        }

        if ($user->id == $litter->creator_id || $user->id == $litter->owner->user_id) {
            $latestApprovalRequest = $litterApprovalRequest->litter->latestApprovalRequest;

            if (!$latestApprovalRequest || !$latestApprovalRequest->exists()) {
                return true;
            }

            if ($latestApprovalRequest->state === 'Rejected') {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param LitterApprovalRequest $litterApprovalRequest
     * @return mixed
     */
    public function update(User $user, LitterApprovalRequest $litterApprovalRequest)
    {
        // User must have this permission in order to update the litter approval request
        if ($user->cant('answer to litter requests')) {
            return false;
        }

        $litterApprovalRequestToUpdate = LitterApprovalRequest::findOrFail($litterApprovalRequest->id);

        // Litter request must have Sent state in order to update it
        if ($litterApprovalRequestToUpdate->state !== 'Sent') {
            return false;
        }

        return true;
    }

    /**
     * Determine, whether litter has all data available before creating new litter approval request
     * @param $litter
     * @return bool
     */
    private function hasRequiredData($litter) {
        if (!$litter->father_id || !$litter->mother_id || !$litter->birthdate ||
            $litter->babies_born === null || $litter->babies_reared === null ||
            $litter->reared_boys === null || $litter->reared_girls === null) {
            return false;
        }

        return true;
    }
}
