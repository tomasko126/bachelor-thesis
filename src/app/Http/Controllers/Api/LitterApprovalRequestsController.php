<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LitterApprovalRequest;
use App\Note;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LitterApprovalRequestsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $requestData = $request->all();

        $this->authorize('create', new LitterApprovalRequest($requestData));

        $request->validate(LitterApprovalRequest::getValidationRules());

        $litterApprovalRequest = LitterApprovalRequest::createLitterApprovalRequest($requestData);

        // Send email about created litter approval request
        LitterApprovalRequest::sendRequestCreatedEmail($request->user()->name, $requestData['litter_id']);

        return response()->json($litterApprovalRequest, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param LitterApprovalRequest $litterapprovalrequest
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, LitterApprovalRequest $litterapprovalrequest)
    {
        $this->authorize('update', $litterapprovalrequest);

        $request->validate(LitterApprovalRequest::getValidationRules());

        $data = $request->all();

        // Create registrator note
        Note::createNoteFromLitterApprovalRequest($data);

        // Update litter's approval request
        $updated = LitterApprovalRequest::updateLitterApprovalRequest($data);

        if (!$updated) {
            return response()->json(null, 500);
        }

        // Send email to the litter request creator about the status change
        LitterApprovalRequest::sendRequestRepliedEmail($data['id']);

        $litterapprovalrequest = $litterapprovalrequest->refresh();
        return response()->json($litterapprovalrequest, 204);
    }
}
