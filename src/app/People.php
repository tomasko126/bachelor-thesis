<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Throwable;

class People extends Model
{
    use SoftDeletes, SoftDeletes;

    protected $guarded = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['creator_id', 'created_at', 'updated_at', 'deleted_at'];

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function breederOfAnimals() {
        return $this->hasMany('App\Animal', 'breeder_id');
    }

    public function ownerOfAnimals() {
        return $this->hasMany('App\Animal', 'owner_id');
    }

    public function ownerOfLitters() {
        return $this->hasMany('App\People', 'owner_id');
    }

    public function station() {
        return $this->belongsTo('App\Station');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Create new person
     *
     * @param $data
     * @param $savedModel
     * @throws Throwable
     */
    public static function createPerson($data, &$savedModel) {
        $user = new People($data);
        $user->creator_id = Auth::id();

        $user->saveOrFail();

        $savedModel = $user->refresh();
    }

    /**
     * Search people by name
     *
     * @param string $keyword
     * @param string $field
     * @param string $order
     * @return Builder[]|Collection
     */
    public static function search(string $keyword = '%%', string $field = 'id', string $order = 'desc') {
        return self::query()->where('name', 'like', $keyword)->orderBy($field, $order)->get();
    }

    public static function getValidationRules() {
        return [
            'station_id' => ['present', 'nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['present', 'nullable', 'email', 'max:255'],
            'telephone_number' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }
}
