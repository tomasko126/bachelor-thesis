<?php

namespace App;

use App\Mail\LitterRequestCreated;
use App\Mail\LitterRequestReplied;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class LitterApprovalRequest extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'creator_id', 'registrator_id', 'created_at', 'updated_at'];
    protected $hidden = ['creator_id'];

    public function litter() {
        return $this->belongsTo('App\Litter');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function registrator() {
        return $this->belongsTo('App\User', 'registrator_id');
    }

    /**
     * Create new litter approval request
     *
     * @param array $data
     * @return LitterApprovalRequest
     * @throws Throwable
     */
    public static function createLitterApprovalRequest(array $data) {
        $litterApprovalRequest = new LitterApprovalRequest($data);
        $litterApprovalRequest->creator_id = Auth::id();

        $litterApprovalRequest->saveOrFail();

        return $litterApprovalRequest->refresh();
    }

    /**
     * Update litter's approval request
     *
     * @param array $data
     * @return bool
     */
    public static function updateLitterApprovalRequest(array $data) {
        // Assign user's ID as a registrator
        $data['registrator_id'] = Auth::id();

        $updatedLitterApprovalRequest = LitterApprovalRequest::where('id', '=', $data['id']);

        return $updatedLitterApprovalRequest->update($data);
    }

    /**
     * Get all litter's approval requests
     *
     * @param int $litterId
     * @return Builder[]|Collection
     */
    public static function getRequestsForLitter(int $litterId) {
        return self::query()->where('litter_id', $litterId)->orderByDesc('id')->get();
    }

    /**
     * Send email about new litter approval request to admin and litter requests approvers
     * @param string $requestedBy
     * @param int $litterId
     */
    public static function sendRequestCreatedEmail(string $requestedBy, int $litterId) {
        // Get litter
        $litter = Litter::with('creator')->findOrFail($litterId);

        // Send message to admin and every litter request approver,
        // that some litter request has been created
        $admins = User::role('admin')->get()->pluck('email')->toArray();

        foreach ($admins as $admin) {
            if (App::environment('production')) {
                Mail::to($admin)->send(new LitterRequestCreated($litter, $requestedBy));
            }

            Mail::mailer('log')->to($admin)->send(new LitterRequestCreated($litter, $requestedBy));
        }

        $litter_request_approvers = User::role('litters requests approver')->get()->pluck('email')->toArray();

        foreach ($litter_request_approvers as $approver) {
            if (App::environment('production')) {
                Mail::to($approver)->send(new LitterRequestCreated($litter, $requestedBy));
            }

            Mail::mailer('log')->to($approver)->send(new LitterRequestCreated($litter, $requestedBy));
        }
    }

    /**
     * Send email about changed status of the litter request to the litter request creator
     *
     * @param int $litterApprovalRequestId
     */
    public static function sendRequestRepliedEmail(int $litterApprovalRequestId) {
        $litterApprovalRequest = self::with(['registrator', 'creator'])->findOrFail($litterApprovalRequestId);

        $litter = Litter::with('creator')->findOrFail($litterApprovalRequest['litter_id']);

        if (App::environment('production')) {
            Mail::to($litterApprovalRequest->creator->email)->send(new LitterRequestReplied($litter, $litterApprovalRequest));
        }

        Mail::mailer('log')->to($litterApprovalRequest->creator->email)->send(new LitterRequestReplied($litter, $litterApprovalRequest));
    }

    public static function getValidationRules() {
        $rules = [
            'id' => ['sometimes', 'integer'],
            'litter_id' => ['required', 'integer'],
        ];

        $json = json_decode(request()->getContent(), true);
        $requestIdExists = array_key_exists('id', $json);

        // We are updating the approval request
        if ($requestIdExists) {
            $rules['state'] = ['required', 'string', Rule::in(['Approved', 'Rejected'])];

            $selectedState = $json['state'];
            if ($selectedState === 'Approved') {
                $rules['registration_date'] = ['required', 'string', 'max:10'];
                $rules['registration_number'] = ['required', 'string', 'max:255'];
            }

            $rules['creator_note'] = ['not_present'];
            $rules['registrator_note'] = ['present', 'nullable', 'string', 'max:255'];
            $rules['litter_registrator_note'] = ['present', 'nullable', 'string', 'max:255'];
        } else {
            $rules['state'] = ['required', 'string', Rule::in(['Sent'])];

            $rules['creator_note'] = ['present', 'nullable', 'string', 'max:255'];
            $rules['registrator_note'] = ['not_present'];
            $rules['litter_registrator_note'] = ['not_present'];
            $rules['registration_number'] = ['not_present'];
            $rules['registration_date'] = ['not_present'];
        }

        return $rules;
    }
}
