<?php

namespace App\Http\Controllers\Api;

use App\AnimalRegistration;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AnimalRegistrationsController extends Controller
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
        $this->authorize('create', new AnimalRegistration($request->all()));

        $request->validate(AnimalRegistration::getValidationRules());

        $registration = AnimalRegistration::createAnimalRegistration($request->all());

        return response()->json($registration, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param AnimalRegistration $animalregistration
     * @return JsonResponse
     */
    public function show(AnimalRegistration $animalregistration)
    {
        return response()->json($animalregistration);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AnimalRegistration $animalregistration
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, AnimalRegistration $animalregistration)
    {
        $updatedRegistration = new AnimalRegistration($request->all());

        $this->authorize('update', $updatedRegistration);

        $request->validate(AnimalRegistration::getValidationRules());

        $updated = $animalregistration->update($request->all());

        if (!$updated) {
            return response()->json(null, 500);
        }

        $animalregistration = $animalregistration->refresh();

        return response()->json($animalregistration, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AnimalRegistration $animalregistration
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AnimalRegistration $animalregistration)
    {
        $this->authorize('delete', $animalregistration);

        if (!$animalregistration->delete()) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }

    /**
     * Get all clubs for animal registration
     *
     * @return JsonResponse
     */
    public function getClubs() {
        $clubs = AnimalRegistration::getClubs();

        return response()->json($clubs);
    }

    /**
     * Get all types of registration
     *
     * @return JsonResponse
     */
    public function getTypes() {
        $regTypes = AnimalRegistration::getRegistrationTypes();

        return response()->json($regTypes);
    }
}
