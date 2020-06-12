<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Station extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'creator_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function people() {
        return $this->hasMany('App\People', 'station_id');
    }

    public function creator() {
        return $this->belongsTo('App\User', 'creator_id');
    }

    /**
     * Create new station
     *
     * @param $data
     * @param $savedModel
     * @throws Throwable
     */
    public static function createStation($data, &$savedModel) {
        $station = new Station($data);
        $station->creator_id = Auth::id();

        $station->saveOrFail();

        $savedModel = $station->refresh();
    }

    /**
     * Search stations by keyword
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
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
