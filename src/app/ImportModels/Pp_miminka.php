<?php

namespace App\ImportModels;

use App\Litter;
use App\People;

class Pp_miminka extends AbstractImport
{
    protected $table = 'pp_miminka';

    public static function convertName($animal) {
        return !empty($animal->mimi_jmeno) ? $animal->mimi_jmeno : null;
    }

    public static function convertSex($animal) {
        return $animal->mimi_pohlavi === 'M' ? 'Male' : 'Female';
    }

    public static function convertBirthdate($litterIdInSystem) {
        $litter = Litter::find($litterIdInSystem);

        if (!$litter) {
            return null;
        }

        return !empty($litter->birthdate) ? $litter->birthdate : null;
    }

    public static function convertEarType($animal) {
        switch ($animal->mimi_typ_ucha) {
            case 'S': return 'Standart';
            case 'DG': return 'Standart Dg';
            case 'D': return 'Dumbo';
            default: return null;
        }
    }

    public static function convertFurType($animal) {
        return !empty($animal->mimi_typ_srsti) ? $animal->mimi_typ_srsti : '';
    }

    public static function convertFurColor($animal) {
        return !empty($animal->mimi_barva_srsti) ? $animal->mimi_barva_srsti : '';
    }

    public static function convertMarkings($animal) {
        return !empty($animal->mimi_znaky) ? $animal->mimi_znaky : '';
    }

    public static function convertEyesColor($animal) {
        switch ($animal->mimi_barva_oci) {
            case 'BL': return 'Black';
            case 'DR': return 'Dark Ruby';
            case 'RB': return 'Ruby';
            case 'RD': return 'Red';
            case 'PI': return 'Pink';
            case 'OE': return 'Odd eyed';
            default: return null;
        }
    }

    public static function processBreeder($animal) {
        $litterId = $animal->id_pp;

        if (empty($litterId)) {
            return null;
        }

        $litter = Pp_informace::find($litterId);

        if (!$litter) {
            return null;
        }

        return self::processWPUser($litter->uzivatel, People::class);
    }

    public static function processOwner($animal) {
        $owner = People::query()->where('name', $animal->mimi_majitel);

        if ($owner->exists()) {
            return $owner->first()->id;
        }

        return null;
    }

    public static function processMother($litterIdInSystem) {
        $litter = Litter::find($litterIdInSystem);

        if (!$litter) {
            return null;
        }

        return $litter->mother_id;
    }

    public static function processFather($litterIdInSystem) {
        $litter = Litter::find($litterIdInSystem);

        if (!$litter) {
            return null;
        }

        return $litter->father_id;
    }

    public static function convertBreedingAvailable($animal) {
        if (empty($animal->mimi_chov)) {
            return null;
        }

        switch ($animal->mimi_chov) {
            case 'OMEZ':
            case 'ANO': return true;
            case 'NE': return false;
            default: return null;
        }
    }

    public static function convertBreedingLimitation($animal) {
        return !empty($animal->mimi_chov_omezeni) ? $animal->mimi_chov_omezeni : null;
    }
}
