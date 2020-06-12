<?php

namespace App\Http\Controllers\Api;

use App\Animal;
use App\AnimalRegistration;
use App\Http\Controllers\Controller;
use App\Note;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AnimalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $animals = Animal::all();
        return response()->json($animals);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $request->validate(Animal::getValidationRules());

        $animal = Animal::createAnimal($request->all());

        return response()->json($animal, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $animalId
     * @return JsonResponse
     */
    public function show(int $animalId)
    {
        $animal = Animal::getAnimal($animalId);

        if (empty($animal)) {
            return response()->json(null, 404);
        }

        return response()->json($animal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Animal $animal
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $request->validate(Animal::getValidationRules());

        $updated = $animal->update($request->all());

        if (!$updated) {
            return response()->json(null, 500);
        }

        $animal = $animal->refresh();

        return response()->json($animal, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Animal $animal
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Animal $animal)
    {
        $this->authorize('delete', $animal);

        if (!$animal->delete()) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $animalId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function restoreAnimal(int $animalId) {
        $this->authorize('restore', Animal::withTrashed()->findOrFail($animalId));

        $restored = Animal::restoreAnimal($animalId);

        if (!$restored) {
            return response()->json(null, 500);
        }

        return response()->json(null, 205);
    }

    /**
     * Filter out animals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request) {
        $name = $request->get("name");
        $nickname = $request->get("nickname");
        $breeder = $request->get("breeder");
        $owner = $request->get("owner");

        $field = $request->get("sort_field");
        $order = $request->get("sort_order");

        $litters = Animal::filter($name, $nickname, $breeder, $owner, $field, $order);

        return response()->json($litters);
    }

    /**
     * Search for animals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        $keyword = "%" . $request->get("keyword") . "%";
        $field = $request->get("sort_field");
        $order = $request->get("sort_order");
        $sex = $request->get('sex');
        $columnToSearch = $request->get('column');

        $animals = Animal::search($keyword, $field, $order, $sex, $columnToSearch);

        return response()->json($animals);
    }

    /**
     * Get animal's genealogy
     *
     * @param int $animalId
     * @return JsonResponse
     */
    public function getAnimalGenealogy(int $animalId) {
        $animal = Animal::withTrashed()->with(['father', 'mother'])->findOrFail($animalId);

        $result = Animal::getGenealogy($animal, 0);

        return response()->json($result);
    }

    /**
     * Get animal's history (audits)
     *
     * @param int $animalId
     * @return JsonResponse
     */
    public function getAnimalHistory(int $animalId) {
        $audits = Animal::getAnimalAudits($animalId);

        return response()->json($audits);
    }

    /**
     * Get all animal's notes
     *
     * @param Request $request
     * @param int $animalId
     * @return JsonResponse
     */
    public function getAnimalNotes(Request $request, int $animalId) {
        $notes = Note::getNotesForModel($request, Animal::class, $animalId);

        return response()->json($notes);
    }


    /**
     * Get all animal's registrations
     *
     * @param int $animalId
     * @return JsonResponse
     */
    public function getAnimalRegistrations(int $animalId) {
        $registrations = AnimalRegistration::getAnimalRegistrations($animalId);

        return response()->json($registrations);
    }

    /**
     * Get all available clubs animal can be still registered in
     *
     * @param int $animalId
     * @return JsonResponse
     */
    public function getAvailableClubsForRegistration(int $animalId) {
        $registeredClubs = AnimalRegistration::getRegisteredClubsForAnimal($animalId);

        return response()->json($registeredClubs);
    }

    /**
     * Return all available ear types
     *
     * @return JsonResponse
     */
    public function getEarTypes() {
        $earTypes = ['Standart', 'Standart Dg', 'Dumbo'];

        return response()->json($earTypes);
    }

    /**
     * Return all available animal eye colors
     *
     * @return JsonResponse
     */
    public function getEyeColors() {
        $eyeColors = ['Black', 'Dark Ruby', 'Ruby', 'Red', 'Pink', 'Odd eyed'];

        return response()->json($eyeColors);
    }
}
