<?php

namespace App\ImportModels;

use Illuminate\Database\Eloquent\Model;

class Pp_informace_orig extends Model
{
    protected $table = 'pp_informace_orig';

    public static function getBreederName($litterId) {
        $litter = self::find($litterId);

        if (!$litter) {
            return null;
        }

        return $litter->chovatel;
    }

    public static function getBreederContact($litterId) {
        $litter = self::find($litterId);

        if (!$litter) {
            return null;
        }

        return $litter->chov_kontakt;
    }
}
