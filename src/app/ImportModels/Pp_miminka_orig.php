<?php

namespace App\ImportModels;

use Illuminate\Database\Eloquent\Model;

class Pp_miminka_orig extends Model
{
    protected $table = 'pp_miminka_orig';

    public static function getOwnerName($animalId) {
        $animal = self::find($animalId);

        if (!$animal) {
            return null;
        }

        return $animal->mimi_majitel;
    }

    public static function getOwnerContact($animalId) {
        $animal = self::find($animalId);

        if (!$animal) {
            return null;
        }

        return $animal->mimi_maj_kontakt;
    }

    public static function getOwnerMemberCardNumber($animalId) {
        $animal = self::find($animalId);

        if (!$animal) {
            return null;
        }

        return $animal->mimi_maj_prukaz;
    }
}
