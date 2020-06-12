<?php

namespace App\ImportModels;

use App\Animal;
use App\AnimalRegistration;
use App\LitterApprovalRequest;
use App\Note;
use App\People;
use App\User;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Czkp_zvire extends AbstractImport
{
    protected $table = 'czkp_zvire';

    // Convert record creation timestamp
    private static function convertCreatedAt($animal) {
        return $animal->datum_zaznamu;
    }

    // Convert animal's sex
    private static function convertSex($animal) {
        return $animal->pohlavi === 'M' ? 'Male' : 'Female';
    }

    // Convert animal's birthdate
    private static function convertBirthdate($animal) {
        if (empty($animal->datum_narozeni)) {
            return null;
        }

        return str_replace(' ', '', $animal->datum_narozeni);
    }

    // Convert animal's name
    private static function convertName($animal) {
        return !empty($animal->jmeno) ? $animal->jmeno : null;
    }

    // Convert animal's nickname
    private static function convertNickname($animal) {
        return !empty($animal->prezdivka) ? $animal->prezdivka : null;
    }

    // Convert animal's eyes color
    private static function convertEyesColor($animal) {
        switch ($animal->barva_oci) {
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
    private static function convertEarType($animal) {
        switch ($animal->typ_ucha) {
            case 'S': return 'Standart';
            case 'DG': return 'Standart Dg';
            case 'D': return 'Dumbo';
            default: return null;
        }
    }

    // Convert animal's fur type
    private static function convertFurType($animal) {
        return !empty($animal->typ_srsti) ? $animal->typ_srsti : '';
    }

    // Convert animal's markings
    private static function convertMarkings($animal) {
        return !empty($animal->bila_kresba) ? $animal->bila_kresba : '';
    }

    // Convert animal's fur color
    public static function convertFurColor($animal) {
        return !empty($animal->barva_srsti) ? $animal->barva_srsti : '';
    }

    // Convert animal's death date
    private static function convertDeathDate($animal) {
        return !empty($animal->datum_umrti) ? $animal->datum_umrti : null;
    }

    // Convert animal's death reason
    private static function convertDeathReason($animal) {
        return !empty($animal->duvod_umrti) ? $animal->duvod_umrti : null;
    }

    // Convert animal's mother
    private static function convertMother($animal) {
        if (empty($animal->matka)) {
            return null;
        }

        $mother = self::query()->where('id_prvni_verze', $animal->matka)->first();

        return self::createAnimal($mother);
    }

    // Convert animal's father
    private static function convertFather($animal) {
        if (empty($animal->otec)) {
            return null;
        }

        $father = self::query()->where('id_prvni_verze', $animal->otec)->first();

        return self::createAnimal($father);
    }

    // Process animal's notes from 'riziko'
    private static function processNote($animal, $animalId) {
        if ($animal->rizikovost_chovu === 'OK') {
            return null;
        }

        $category = $animal->rizikovost_chovu === 'R' ? 'warning' : 'alert';

        Note::createNote([
            'note' => $animal->riziko_pozn,
            'public' => true,
            'category' => $category,
            'animal_id' => $animalId,
        ]);

        return true;
    }

    // Process animal's CZKP registration
    private static function processCZKPRegistration($animal, $animalId) {
        if (empty($animal->reg_ckp_typ)) {
            return null;
        }

        $created_at = null;

        if (!empty($animal->datum_registrace)) {
            $animal->datum_registrace = str_replace(' ', '', $animal->datum_registrace);
            $created_at = (new DateTime($animal->datum_registrace))->format('Y-m-d H:i:s');
        }

        $breeding_not_available = !empty($animal->omezeni_reg) ? (strpos($animal->omezeni_reg, 'výstavy') !== false) : false;

        $registration = new AnimalRegistration();
        $registration->animal_id = $animalId;
        $registration->club = 'CZKP';
        $registration->registration_number = $animal->reg_ckp_cislo;
        $registration->year = $animal->reg_ckp_rok;
        $registration->breeding_limitation = $animal->omezeni_reg;
        $registration->breeding_available = !$breeding_not_available;
        $registration->type = $animal->reg_ckp_typ;
        $registration->created_at = $created_at;
        $registration->registrator_id = self::processWPUser($animal->registrator, User::class) ?? Auth::id();

        $registration->saveOrFail();
        $registration->refresh();

        return $registration->id;
    }

    // Process animal's Other registration
    private static function processOtherRegistration($animal, $animalId) {
        if (empty($animal->reg_c_ostatni)) {
            return null;
        }

        $created_at = null;

        if (!empty($animal->datum_registrace)) {
            $animal->datum_registrace = str_replace(' ', '', $animal->datum_registrace);
            $created_at = (new DateTime($animal->datum_registrace))->format('Y-m-d H:i:s');
        }

        $breeding_not_available = !empty($animal->omezeni_reg) ? (strpos($animal->omezeni_reg, 'výstavy') !== false) : false;

        $registration =  new AnimalRegistration();
        $registration->animal_id = $animalId;
        $registration->club = 'Other';
        $registration->registration_number = $animal->reg_c_ostatni;
        $registration->year = null;
        $registration->breeding_limitation = $animal->omezeni_reg;
        $registration->breeding_available = !$breeding_not_available;
        $registration->type = null;
        $registration->registrator_id = self::processWPUser($animal->registrator, User::class) ?? Auth::id();
        $registration->created_at = $created_at;

        $registration->saveOrFail();
        $registration->refresh();

        return $registration->id;
    }

    // Process animal's creator
    private static function processCreator($animal) {
        return self::processWPUser($animal->uzivatel, User::class);
    }

    // Process animal's breeder
    private static function processBreeder($animal) {
        if (!empty($animal->chovatel_ckp_id)) {
            return self::processWPUser($animal->chovatel_ckp_id, People::class);
        }

        $personName = $animal->chovatel;

        if (empty($personName)) {
            return null;
        }

        $person = People::query()->where('name', $personName);

        if (!$person->exists()) {
            return null;
        }

        $person = $person->first();

        return $person->id;
    }

    // Process animal's owner
    private static function processOwner($animal) {
        if (!empty($animal->majitel_ckp_id)) {
            return self::processWPUser($animal->majitel_ckp_id, People::class);
        }

        $personName = $animal->majitel;

        if (empty($personName)) {
            return null;
        }

        $person = People::query()->where('name', $personName);

        if (!$person->exists() || empty($person->first()->user_id)) {
            // If we do not know the name of owner of animal and WP id as well,
            // we assign the owner to the creator of this animal
            return self::processCreator($animal);
        }

        $person = $person->first();

        return $person->user_id;
    }

    // Create animal
    private static function createAnimal($animal) {
        if (Animal::find($animal->id_prvni_verze) !== null) {
            return $animal->id_prvni_verze;
        }

        $newAnimal = new Animal();
        $newAnimal->id = $animal->id_prvni_verze;
        $newAnimal->created_at = self::convertCreatedAt($animal);
        $newAnimal->creator_id = self::processCreator($animal);
        $newAnimal->breeder_id = self::processBreeder($animal);
        $newAnimal->owner_id = self::processOwner($animal);
        $newAnimal->mother_id = self::convertMother($animal);
        $newAnimal->father_id = self::convertFather($animal);
        $newAnimal->name = self::convertName($animal);
        $newAnimal->nickname = self::convertNickname($animal);
        $newAnimal->sex = self::convertSex($animal);
        $newAnimal->eyes_color = self::convertEyesColor($animal);
        $newAnimal->ear_type = self::convertEarType($animal);
        $newAnimal->birthdate = self::convertBirthdate($animal);
        $newAnimal->fur_type = self::convertFurType($animal);
        $newAnimal->fur_color = self::convertFurColor($animal);
        $newAnimal->markings = self::convertMarkings($animal);
        $newAnimal->death_date = self::convertDeathDate($animal);
        $newAnimal->death_reason = self::convertDeathReason($animal);

        $newAnimal->saveOrFail();

        $newAnimal->refresh();

        // Create notes for animal
        self::processNote($animal, $animal->id_prvni_verze);

        // Create animal's registrations
        self::processCZKPRegistration($animal, $animal->id_prvni_verze);
        self::processOtherRegistration($animal, $animal->id_prvni_verze);

        return $animal->id_prvni_verze;
    }

    // Link animal with litter (with only litter having czkp registration)
    private static function addLitter($animal) {
        $animalId = $animal->id_prvni_verze;

        $litterRegistrationNumber = $animal->cis_vrhu;

        if (Str::startsWith($litterRegistrationNumber, ['CKP', 'CKPN'])) {
            // We are dealing with the CKP registration

            // Now, find the animal with the same ID in the animals table
            $animal = Animal::findOrFail($animalId);

            $litterApprovalRequest = LitterApprovalRequest::query()->where('registration_number', $litterRegistrationNumber);

            // If litter approval request exists with such a registration number,
            // we will connect the animal with the litter_id found in the litter approval request
            if ($litterApprovalRequest->exists()) {
                $litterApprovalRequest = $litterApprovalRequest->first();

                $animal->litter_id = $litterApprovalRequest->litter_id;
                $animal->saveOrFail();

                return;
            }
        }
    }

    // Import animals from czkp_zvire table
    public static function import() {
        // Get latest version of every animal
        $animalsIds = self::query()
            ->select([DB::raw('max(id) as id'), 'id_prvni_verze'])
            ->groupBy('id_prvni_verze');

        $animalsIds = $animalsIds->pluck('id_prvni_verze');

        $animals = self::query()->whereIn('id', $animalsIds)->get();

        foreach ($animals as $animal) {
            self::createAnimal($animal);
        }
    }

    public static function connectWithLitters() {
        // Get latest version of every animal
        $animalsIds = self::query()
            ->select([DB::raw('max(id) as id'), 'id_prvni_verze'])
            ->groupBy('id_prvni_verze');

        $animalsIds = $animalsIds->pluck('id_prvni_verze');

        $animals = self::query()->whereIn('id', $animalsIds)->get();

        foreach ($animals as $animal) {
            self::addLitter($animal);
        }
    }
}
