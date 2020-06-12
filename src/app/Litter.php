<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Contracts\Auditable;
use Throwable;

class Litter extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $guarded = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['created_at', 'updated_at'];

    protected static function booted()
    {
        self::saving(function (Litter $litter) {
            // Owner of the litter should be the current user, if it is empty
            if (empty($litter->owner_id)) {
                $userId = Auth::id();

                // Get user from people table
                $people = People::query()->where('user_id', '=', $userId)->get(['id'])->first();

                // Set owner as user from people table
                $litter->owner_id = $people->id;
            }
        });
    }

    public function owner() {
        return $this->belongsTo('App\People', 'owner_id');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function father() {
        return $this->belongsTo('App\Animal', 'father_id');
    }

    public function mother() {
        return $this->belongsTo('App\Animal', 'mother_id');
    }

    public function animals() {
        return $this->hasMany('App\Animal', 'litter_id');
    }

    public function notes() {
        return $this->hasMany('App\Note', 'litter_id');
    }

    public function approvalRequests() {
        return $this->hasMany('App\LitterApprovalRequest', 'litter_id');
    }

    public function latestApprovalRequest() {
        return $this->hasOne('App\LitterApprovalRequest', 'litter_id')->latest('id');
    }

    /**
     * Create new litter
     *
     * @param $data
     * @return Litter
     * @throws Throwable
     */
    public static function createLitter($data) {
        $litter = new Litter($data);
        $litter->creator_id = Auth::id();

        $litter->saveOrFail();

        return $litter->refresh();
    }

    /**
     * Get litter by its id
     * This method does not check whether user can see this litter
     * This is handled separately in LitterPolicy
     *
     * @param int $id
     * @return array|mixed
     */
    public static function getLitter(int $id) {
        $litter = self::withTrashed()->with(['owner.station', 'mother', 'father', 'latestApprovalRequest.registrator'])->findOrFail($id);

        $user = request()->user();

        if ($user->id != $litter->creator_id && $user->id != $litter->owner->user_id && !$user->hasRole('admin')) {
            // We mask sensitive data when retrieving litter
            // by another user than either it's creator/owner or admin
            $litter->breeder_name = '**************';
            $litter->breeder_contact = '**************';
        }

        // Calculate litter varieties
        $litterVarieties = self::getLitterVarieties($id);
        $litter->varieties = $litterVarieties;

        return $litter;
    }

    /**
     * Restore given litter
     *
     * @param int $id
     * @return bool|null
     */
    public static function restoreLitter(int $id) {
        $litterToRestore = self::onlyTrashed()->findOrFail($id);
        return $litterToRestore->restore();
    }

    /**
     * Get all animals in litter
     *
     * @param int $id
     * @return array
     */
    public static function getAnimalsForLitter(int $id) {
        $litterWithAnimals = self::getLittersWithPermissions()->with(['animals', 'animals.owner'])->get()->where('id', '=', $id)->first();

        return !empty($litterWithAnimals) ? $litterWithAnimals['animals'] : [];
    }

    /**
     * Get all litters respecting permissions
     *
     * @return Builder]
     */
    public static function getLittersWithPermissions() {
        $user = User::find(Auth::id());

        $result = self::query()
            ->select(['litters.*', 'litter_approval_requests.state', 'litter_approval_requests.registration_number'])
            ->leftJoin('litter_approval_requests', function ($join) {
                $join
                    ->on('litters.id', '=', 'litter_approval_requests.litter_id')
                    ->where('litter_approval_requests.id', DB::raw("(SELECT max(id) from litter_approval_requests WHERE litter_approval_requests.litter_id = litters.id)"));
            })
            ->leftJoin('people', 'litters.owner_id', '=', 'people.id');

        if ($user->cant('see not approved litters')) {
            $result = $result
                    ->where(function ($query) {
                        // Get user from people table
                        $people = People::query()->where('user_id', '=', Auth::id())->get(['id'])->first();

                        $query
                            ->where('litters.type', '=', 'VP')->orWhere(function ($q) {
                                $q->where('litter_approval_requests.state','=', 'Approved')
                                    ->whereIn('litters.type', ['PP', 'NV']);
                            })
                            ->orWhere('litters.creator_id', '=', Auth::id())
                            ->orWhere('litters.owner_id', '=', $people->id);
                });
        }

        return $result;
    }

    /**
     * Get litter's history (audits)
     *
     * @param $litterId
     * @return array
     */
    public static function getLitterHistory($litterId) {
        $litter = self::withTrashed()->findOrFail($litterId);

        $audits = $litter->audits->sortDesc();

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
     * Get litter's varieties
     *
     * @param int $id
     * @return string
     */
    public static function getLitterVarieties(int $id) {
        $animalsInLitter = Animal::query()->where('litter_id', '=', $id)->get();

        $varieties = [
            'eyes_color' => [],
            'ear_type' => [],
            'fur_color' => [],
            'fur_type' => [],
            'markings' => [],
        ];

        foreach ($animalsInLitter as $animal) {
            if (!in_array($animal->eyes_color, $varieties['eyes_color'])) {
                $varieties['eyes_color'][] = $animal->eyes_color;
            }

            if (!in_array($animal->ear_type, $varieties['ear_type'])) {
                $varieties['ear_type'][] = $animal->ear_type;
            }

            if (!in_array($animal->fur_color, $varieties['fur_color'])) {
                $varieties['fur_color'][] = $animal->fur_color;
            }

            if (!in_array($animal->fur_type, $varieties['fur_type'])) {
                $varieties['fur_type'][] = $animal->fur_type;
            }

            if (!in_array($animal->markings, $varieties['markings'])) {
                $varieties['markings'][] = $animal->markings;
            }
        }

        return
            implode(', ', $varieties['eyes_color']) . ' / ' .
            implode(', ', $varieties['ear_type']) . ' / ' .
            implode(', ', $varieties['fur_color']) . ' / ' .
            implode(', ', $varieties['fur_type']) . ' / ' .
            implode(', ', $varieties['markings']);
    }

    /**
     * Filter litters according to the user's filter
     * @param string|null $label
     * @param string|null $owner
     * @param string|null $state
     * @param string|null $type
     * @param string $field
     * @param string $order
     * @return LengthAwarePaginator]
     */
    public static function filter(?string $label, ?string $owner, ?string $state, ?string $type, string $field = 'id', string $order = 'desc') {

        $results = self::getLittersWithPermissions();

        if ($label) {
            $results = $results->where('litters.label', '=', $label);
        }

        if ($owner) {
            $results = $results->where('people.name', '=', $owner);
        }

        if ($type) {
            $results = $results->where('litters.type', '=', $type);
        }

        if ($state) {
            $results = $results->where('litter_approval_requests.state', '=', $state);
        }

        $results = $results
            ->groupBy('litters.id', 'litter_approval_requests.state', 'litter_approval_requests.registration_number', 'people.name')
            ->with(['latestApprovalRequest', 'owner'])->orderBy($field, $order);

        return $results->paginate(10);
    }

    /**
     * Search litters by keyword
     *
     * @param string $keyword
     * @param string $field
     * @param string $order
     * @return Builder[]|Collection
     */
    public static function search(string $keyword = '%%', string $field = 'id', string $order = 'desc') {
        return self::getLittersWithPermissions()->with(['mother', 'father'])->where('label', 'like', $keyword)->orderBy($field, $order)->get();
    }

    public static function getValidationRules() {
        $types = ['VP', 'PP', 'NV'];

        $rules = [
            'owner_id' => ['present', 'nullable', 'integer'],
            'father_id' => ['present', 'nullable', 'integer'],
            'mother_id' => ['present', 'nullable', 'integer'],
            'birthdate' => ['present', 'nullable', 'string', 'max:255'],
            'label' => ['present', 'nullable', 'string', 'max:255'],
            'line' => ['present', 'nullable', 'string', 'max:255'],
            'genetic_information' => ['present', 'nullable', 'string', 'max:255'],
            'babies_born' => ['present', 'nullable', 'integer'],
            'babies_reared' => ['present', 'nullable', 'integer'],
            'reared_boys' => ['present', 'nullable', 'integer'],
            'reared_girls' => ['present', 'nullable', 'integer'],
            'for_breeding' => ['present', 'nullable', 'integer'],
            'for_petting' => ['present', 'nullable', 'integer'],
            'type' => ['required', 'string', Rule::in($types)],
        ];

        // If user can't add foreign owner to litter,
        // make sure that owner is only the creator himself
        if (!request()->user()->can('add foreign owner to litter')) {
            $currentLitter = Litter::find(request()->id);
            $updatedLitter = request();

            if (!$currentLitter || $currentLitter->owner_id != $updatedLitter->owner_id) {
                // Get user from people table
                $people = People::query()->where('user_id', '=', Auth::id())->get(['id'])->first();

                $rules['owner_id'][] = Rule::in($people->id);
            }
        }

        return $rules;
    }
}
