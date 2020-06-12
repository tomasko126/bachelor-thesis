<?php

namespace App\ImportModels;

use App\Animal;
use App\Litter;
use App\People;
use Illuminate\Support\Facades\Auth;

class Czkp_mimi extends AbstractImport
{
    protected $table = 'czkp_mimi';

    private static function processOwner($baby) {
        return self::processBreeder($baby);
    }

    private static function processBreeder($baby) {
        $name = $baby->chov_kdo;

        $person = People::query()->where('name', $name);

        if (!$person->exists()) {
            return null;
        }

        $person = $person->first();

        return $person->id;
    }

    // Get animal's mother_id
    private static function processMother($baby) {
        if (!empty($baby->id_vrhu)) {
            return null;
        }

        $litter = Litter::find($baby->id_vrhu);

        if (!$litter) {
            return null;
        }

        return $litter->mother_id;
    }

    // Get animal's father_id
    private static function processFather($baby) {
        if (!empty($baby->id_vrhu)) {
            return null;
        }

        $litter = Litter::find($baby->id_vrhu);

        if (!$litter) {
            return null;
        }

        return $litter->father_id;
    }

    // Convert animal's litter
    private static function convertLitter($baby) {
        return !empty($baby->id_vrhu) ? $baby->id_vrhu : null;
    }

    // Convert animal's name
    private static function convertName($baby) {
        return !empty($baby->jmeno) ? $baby->jmeno : '';
    }

    // Convert animal's sex
    private static function convertSex($baby) {
        return $baby->pohlavi === 'M' ? 'Male' : 'Female';
    }

    // Convert animal's eyes color
    private static function convertEyesColor($baby) {
        switch ($baby->barva_oci) {
            case 'BL': return 'Black';
            case 'DR': return 'Dark Ruby';
            case 'RB': return 'Ruby';
            case 'RD': return 'Red';
            case 'PI': return 'Pink';
            case 'OE': return 'Odd eyed';
            default: return null;
        }
    }

    // Convert animal's ear type
    private static function convertEarType($baby) {
        switch ($baby->typ_ucha) {
            case 'S': return 'Standart';
            case 'DG': return 'Standart Dg';
            case 'D': return 'Dumbo';
            default: return null;
        }
    }

    // Convert animal's fur type
    private static function convertFurType($baby) {
        return !empty($baby->typ_srsti) ? $baby->typ_srsti : '';
    }

    // Convert animal's fur color
    public static function convertFurColor($baby) {
        return !empty($baby->barva_srsti) ? $baby->barva_srsti : '';
    }

    // Convert animal's markings
    private static function convertMarkings($baby) {
        return !empty($baby->znaky) ? $baby->znaky : '';
    }

    private static function convertBreedingAvailable($baby) {
        if (empty($baby->chov)) {
            return null;
        }

        return $baby->chov === 'ANO';
    }

    private static function convertBreedingLimitation($baby) {
        return !empty($baby->chov_omez) ? $baby->chov_omez : null;
    }

    private static function addBaby($baby) {
        $animal = new Animal();
        $animal->creator_id = Auth::id();
        $animal->breeder_id = self::processBreeder($baby);
        $animal->owner_id = self::processOwner($baby);
        $animal->mother_id = self::processMother($baby);
        $animal->father_id = self::processFather($baby);
        $animal->litter_id = self::convertLitter($baby);
        $animal->name = self::convertName($baby);
        $animal->sex = self::convertSex($baby);
        $animal->eyes_color = self::convertEyesColor($baby);
        $animal->ear_type = self::convertEarType($baby);
        $animal->fur_type = self::convertFurType($baby);
        $animal->fur_color = self::convertFurColor($baby);
        $animal->markings = self::convertMarkings($baby);
        $animal->breeding_available = self::convertBreedingAvailable($baby);
        $animal->breeding_limitation = self::convertBreedingLimitation($baby);

        $animal->saveOrFail();
    }

    public static function import() {
        $babies = self::all();

        foreach ($babies as $baby) {
            self::addBaby($baby);
        }
    }
}
