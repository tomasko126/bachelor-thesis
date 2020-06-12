<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Contracts\Auditable;
use Throwable;

class Animal extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $guarded = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['created_at', 'updated_at'];

    protected static function booted()
    {
        self::saving(function (Animal $animal) {
            // Owner of the animal should be the current user, if it is empty
            if (empty($animal->owner_id)) {
                $userId = Auth::id();

                // Get user from people table
                $people = People::query()->where('user_id', '=', $userId)->get(['id'])->first();

                // Set owner as user from people table
                $animal->owner_id = $people->id;
            }
        });
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function breeder() {
        return $this->belongsTo('App\People', 'breeder_id');
    }

    public function owner() {
        return $this->belongsTo('App\People', 'owner_id');
    }

    public function mother() {
        return $this->belongsTo('App\Animal', 'mother_id');
    }

    public function father() {
        return $this->belongsTo('App\Animal', 'father_id');
    }

    public function litter() {
        return $this->belongsTo('App\Litter', 'litter_id');
    }

    public function registrations() {
        return $this->hasMany('App\AnimalRegistration', 'animal_id');
    }

    public function fatherOfLitters() {
        return $this->hasMany('App\Litter', 'father_id');
    }

    public function motherOfLitters() {
        return $this->hasMany('App\Litter', 'mother_id');
    }

    public function notes() {
        return $this->hasMany('App\Note', 'animal_id');
    }

    public function czkpRegistration() {
        return $this->hasOne('App\AnimalRegistration', 'animal_id')->where('club', '=', 'CZKP')->latest('id');
    }

    /**
     * Create new animal
     *
     * @param $data
     * @return Animal
     * @throws Throwable
     */
    public static function createAnimal($data) {
        $animal = new Animal($data);
        $animal->creator_id = Auth::id();

        $animal->saveOrFail();

        return $animal->refresh();
    }

    /**
     * Get animal (even deleted) by its id
     *
     * @param int $id
     * @return Animal|Builder|Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getAnimal(int $id) {
        return self::withTrashed()->with(['breeder', 'owner', 'mother', 'father', 'litter', 'registrations'])->findOrFail($id);
    }

    /**
     * Get animal for .pdf generation
     *
     * @param int $id
     * @return Animal|Animal[]|Builder|Builder[]|Collection|Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Builder[]
     */
    public static function getAnimalForPdf(int $id) {
        return self::withTrashed()->with(['breeder', 'owner', 'mother', 'father', 'litter.owner.station', 'registrations.registrator', 'czkpRegistration.registrator'])->findOrFail($id);
    }

    /**
     * Return all system user's animals
     *
     * @param int $userId
     * @return Builder[]|Collection
     */
    public static function getAnimalsOfUser(int $userId) {
        return self::query()->select(['animals.*'])
            ->leftJoin('people as owner', 'animals.owner_id', '=', 'owner.id')
            ->leftJoin('users', 'owner.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->with('owner.user')
            ->get();
    }

    /**
     * Get animal history
     *
     * @param int $id
     * @return array
     */
    public static function getAnimalAudits(int $id) {
        $animal = self::withTrashed()->findOrFail($id);

        $audits = $animal->audits->sortDesc();

        $modifiedAudits = [];
        foreach ($audits as $audit) {
            $modifiedAudits[] = [
                'event' => $audit->event,
                'fired_by' => $audit->user->name,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'created_at' => $audit->created_at->toDateTimeString(),
            ];
        }

        return $modifiedAudits;
    }

    /**
     * Recursive method for getting animal's ancestors
     * @param $model
     * @param int $level
     * @return array|void
     */
    public static function getGenealogy($model, int $level) {
        if (empty($model) || $level > 5) {
            return;
        }

        // Load czkp registration, if model is Animal
        if ($model instanceof Animal) {
            $model = $model->load('czkpRegistration');
        }

        $mother = self::getGenealogy($model->mother, $level + 1);
        $father = self::getGenealogy($model->father, $level + 1);

        return ['animal' => $model, 'father' => $father, 'mother' => $mother];
    }

    /**
     * Restore animal
     *
     * @param int $id
     * @return bool|null
     */
    public static function restoreAnimal(int $id) {
        $animalToRestore = self::onlyTrashed()->findOrFail($id);
        return $animalToRestore->restore();
    }

    /**
     * Filter out animals for animals view
     *
     * @param string|null $name
     * @param string|null $nickname
     * @param string|null $breeder
     * @param string|null $owner
     * @param string $field
     * @param string $order
     * @return LengthAwarePaginator
     */
    public static function filter(?string $name, ?string $nickname, ?string $breeder, ?string $owner, string $field = 'id', string $order = 'desc') {
        $results = self::query()
            ->select(['animals.*'])
            ->leftJoin('people as breeder', 'animals.breeder_id', '=', 'breeder.id')
            ->leftJoin('people as owner', 'animals.owner_id', '=', 'owner.id')
            ->groupBy('animals.id')
            ->with(['registrations', 'owner', 'breeder']);

        if ($name) {
            $results = $results->where('animals.name', '=', $name);
        }

        if ($nickname) {
            $results = $results->where('nickname', '=', $nickname);
        }

        if ($breeder) {
            $results = $results->where('breeder.name', '=', $breeder);
        }

        if ($owner) {
            $results = $results->where('owner.name', '=', $owner);
        }

        return $results->orderBy($field, $order)->paginate(10);
    }

    /**
     * Search for animals by keyword
     *
     * @param string $keyword
     * @param string $field
     * @param string $order
     * @param string $sex
     * @param string $columnToSearch
     * @return Builder[]|Collection
     */
    public static function search(string $keyword = '%%', string $field = 'id', string $order = 'desc', ?string $sex = 'any', string $columnToSearch = 'all') {
        $results = self::query();

        if ($columnToSearch === 'all') {
            $results = $results->where(function ($query) use ($keyword) {
                $query->where('name', 'like', $keyword)->orWhere('nickname', 'like', $keyword);
            });
        } else if ($columnToSearch === 'name') {
            $results = $results->where('name', 'like', $keyword);
        } else if ($columnToSearch === 'nickname') {
            $results = $results->where('nickname', 'like', $keyword);
        }

        if ($sex) {
            $results = $results->where('sex', '=', $sex);
        }

        return $results->orderBy($field, $order)->get();
    }

    public static function getValidationRules() {
        $sex = ['Male', 'Female'];
        $eyesColors = ['Black', 'Dark Ruby', 'Ruby', 'Red', 'Pink', 'Odd eyed'];
        $earTypes = ['Standart', 'Standart Dg', 'Dumbo'];

        $rules = [
            'breeder_id' => ['present', 'nullable', 'integer'],
            'owner_id' => ['present', 'nullable', 'integer'],
            'mother_id' => ['present', 'nullable', 'integer'],
            'father_id' => ['present', 'nullable', 'integer'],
            'litter_id' => ['present', 'nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['present', 'nullable', 'string', 'max:255'],
            'sex' => ['required', 'string', Rule::in($sex)],
            'birthdate' => ['present', 'nullable', 'string'],
            'eyes_color' => ['required', 'string', Rule::in($eyesColors)],
            'ear_type' => ['required', 'string', Rule::in($earTypes)],
            'fur_color' => ['required', 'string', 'max:255'],
            'fur_type' => ['required', 'string', 'max:255'],
            'markings' => ['required', 'string', 'max:255'],
            'death_date' => ['present', 'nullable', 'string', 'max:255'],
            'death_reason' => ['present', 'nullable', 'string', 'max:255'],
        ];

        // If user can't add foreign owner to animal,
        // make sure that owner is only the creator from people table himself
        if (!request()->user()->can('add foreign owner to animal')) {
            $currentAnimal = Animal::find(request()->id);
            $updatedAnimal = request();

            // Only check, when we are creating new animal or owner id has been updated
            if (!$currentAnimal || $currentAnimal->owner_id != $updatedAnimal->owner_id) {
                $userId = Auth::id();

                // Get user from people table
                $people = People::query()->where('user_id', '=', $userId)->get(['id'])->first();

                $rules['owner_id'][] = Rule::in($people->id);
            }
        }

        return $rules;
    }
}
