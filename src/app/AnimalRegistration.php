<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Throwable;

class AnimalRegistration extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'registrator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['updated_at', 'deleted_at'];

    public function registratedAnimal() {
        return $this->belongsTo('App\Animal','animal_id');
    }

    public function registrator() {
        return $this->belongsTo('App\User', 'registrator_id');
    }

    /**
     * Create new animal registration
     *
     * @param $data
     * @return AnimalRegistration
     * @throws Throwable
     */
    public static function createAnimalRegistration($data) {
        $animal = new AnimalRegistration($data);
        $animal->registrator_id = Auth::id();

        $animal->saveOrFail();

        return $animal->refresh();
    }

    /**
     * Get all registration of animal
     *
     * @param int $animalId
     * @return Builder[]|Collection
     */
    public static function getAnimalRegistrations(int $animalId) {
        return self::query()->where('animal_id', $animalId)->get();
    }

    /**
     * Determine, which club can be the given animal registered in
     *
     * @param int $animalId
     * @return array
     */
    public static function getRegisteredClubsForAnimal(int $animalId) {
        $allClubs = self::getClubs();
        $usedClubs = self::query()->where('animal_id', $animalId)->get(['club'])->pluck('club')->toArray();

        $clubs = [];
        foreach ($allClubs as $club) {
            $clubs[$club] = in_array($club, $usedClubs);
        }

        // We can still add another 'Other' registration
        $clubs['Other'] = false;

        return $clubs;
    }

    /**
     * Get all values of clubs
     *
     * @return array|mixed
     */
    public static function getClubs() {
        return ['CZKP', 'SOP', 'Other'];
    }

    /**
     * Get all values of registration types
     *
     * @return array|mixed
     */
    public static function getRegistrationTypes() {
        return ['CZ', 'CZN'];
    }

    public static function getValidationRules() {
        $clubs = self::getClubs();

        $rules = [
            'breeding_available' => ['present', 'nullable', 'boolean'],
            'breeding_limitation' => ['present', 'nullable', 'string', 'max:255'],
            'club' => ['required', 'string', Rule::in($clubs)],
            'registration_number' => ['required', 'string', 'max:255'],
        ];

        $selectedClub = json_decode(request()->getContent())->club;

        if (!empty($selectedClub) && $selectedClub === 'CZKP') {
            $types = self::getRegistrationTypes();

            $rules['type'] = ['required', 'string', Rule::in($types)];
            $rules['year'] = ['required', 'string', 'max:4'];
        }

        return $rules;
    }
}
