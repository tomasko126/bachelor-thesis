<?php

namespace App\Http\Controllers\Api;

use App\Animal;
use App\Http\Controllers\Controller;
use App\Litter;
use App\LitterApprovalRequest;
use App\Note;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LittersController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $request->validate(Litter::getValidationRules());

        $litter = Litter::createLitter($request->all());

        return response()->json($litter, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(int $id)
    {
        $litter = Litter::getLitter($id);

        $this->authorize('view', $litter);

        return response()->json($litter);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Litter $litter
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Litter $litter)
    {
        $this->authorize('update', $litter);

        $request->validate(Litter::getValidationRules());

        $updated = $litter->update($request->all());

        if (!$updated) {
            return response()->json(null, 500);
        }

        $litter = $litter->refresh();

        return response()->json($litter, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Litter $litter
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Litter $litter)
    {
        $this->authorize('delete', $litter);

        $deleted = $litter->delete();

        if (!$deleted) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function restoreLitter(int $litterId) {
        $this->authorize('restore', Litter::withTrashed()->find($litterId));

        $restored = Litter::restoreLitter($litterId);

        if (!$restored) {
            return response()->json(null, 500);
        }

        return response()->json(null, 205);
    }

    /**
     * Filter litters
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request) {
        $label = $request->get("label");
        $owner = $request->get("owner");
        $state = $request->get("state");
        $type = $request->get("type");

        $field = $request->get("sort_field");
        $order = $request->get("sort_order");

        $litters = Litter::filter($label, $owner, $state, $type, $field, $order);

        return response()->json($litters);
    }

    /**
     * Search litters by label
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        $keyword = "%" . $request->get("keyword") . "%";
        $field = $request->get("sort_field");
        $order = $request->get("sort_order");

        $litters = Litter::search($keyword, $field, $order);

        return response()->json($litters);
    }

    /**
     * Get all litter's approval requests
     *
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getLitterApprovalRequests(int $litterId) {
        $litter = Litter::withTrashed()->findOrFail($litterId);

        $this->authorize('viewLitterRequests', $litter);

        $requests = LitterApprovalRequest::getRequestsForLitter($litterId);

        return response()->json($requests);
    }

    /**
     * Get litter's history (audits)
     *
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getLitterHistory(int $litterId) {
        $litter = Litter::withTrashed()->findOrFail($litterId);

        $this->authorize('view', $litter);

        $audits = Litter::getLitterHistory($litterId);

        return response()->json($audits);
    }

    /**
     * Get all animals in litter defined by id parameter
     *
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getLitterAnimals(int $litterId) {
        $litter = Litter::withTrashed()->findOrFail($litterId);

        $this->authorize('view', $litter);

        $animals = Litter::getAnimalsForLitter($litterId);

        return response()->json($animals);
    }

    /**
     * Get litter's genealogy
     *
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getLitterGenealogy(int $litterId) {
        $litter = Litter::withTrashed()->findOrFail($litterId);

        $this->authorize('view', $litter);

        $result = Animal::getGenealogy($litter, 0);

        return response()->json($result);
    }

    /**
     * Get all litter's notes
     *
     * @param Request $request
     * @param int $litterId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function getLitterNotes(Request $request, int $litterId) {
        $this->authorize('view',  Litter::withTrashed()->findOrFail($litterId));

        $notes = Note::getNotesForModel($request, Litter::class, $litterId);

        return response()->json($notes);
    }
}
