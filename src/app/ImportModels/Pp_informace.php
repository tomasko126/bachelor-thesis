<?php

namespace App\ImportModels;

use App\Animal;
use App\Litter;
use App\LitterApprovalRequest;
use App\People;
use App\User;
use DateTime;
use Illuminate\Support\Facades\Auth;

class Pp_informace extends AbstractImport
{
    protected $table = 'pp_informace';

    // This offset is set in order to prevent collisions
    // with already inserted data which were coming from different table
    const LITTER_TABLE_OFFSET = 450;

    private static function convertCreator($litter) {
        return self::processWPUser($litter->uzivatel, User::class);
    }

    private static function processFather($litter) {
        return !empty($litter->vrh_id_otec) ? $litter->vrh_id_otec : null;
    }

    private static function processMother($litter) {
        return !empty($litter->vrh_id_matka) ? $litter->vrh_id_matka : null;
    }

    private static function convertBirthdate($litter) {
        if (empty($litter->vrh_narozeni)) {
            return null;
        }

        return (new DateTime($litter->vrh_narozeni))->format('d.m.Y');
    }

    private static function convertLabel($litter) {
        return !empty($litter->vrh_oznaceni) ? $litter->vrh_oznaceni : null;
    }

    private static function convertBornBabies($litter) {
        return !empty($litter->vrh_nar_mladat) ? $litter->vrh_nar_mladat : 0;
    }

    private static function convertRearedBabies($litter) {
        return !empty($litter->vrh_odch_mladat) ? $litter->vrh_odch_mladat : 0;
    }

    private static function convertRearedGirls($litter) {
        return !empty($litter->vrh_odch_holky) ? $litter->vrh_odch_holky : 0;
    }

    private static function convertRearedBoys($litter) {
        return !empty($litter->vrh_odch_kluci) ? $litter->vrh_odch_kluci : 0;
    }

    private static function convertType($litter) {
        return !empty($litter->typ_vrhu) ? $litter->typ_vrhu : null;
    }

    private static function processOwner($litter) {
        return self::processWPUser($litter->uzivatel, People::class);
    }

    // Is litter duplicate?
    private static function isLitterDuplicate($litter, &$litterId) {
        $fatherId = $litter->vrh_id_otec;
        $motherId = $litter->vrh_id_matka;
        $label = $litter->vrh_oznaceni;

        $litter = Litter::query()
            ->where('mother_id', $motherId)
            ->where('father_id', $fatherId)
            ->where('label', $label);

        if ($litter->exists()) {
            $litterId = $litter->first()->id;
            return true;
        }

        return false;
    }

    private static function updateLitter($litterNotInSystem, $litterIdInSystem) {
        /** @var Litter $litterInSystem */
        $litterInSystem = Litter::findOrFail($litterIdInSystem);

        // Update creator a breeder info
        $litterInSystem->creator_id = self::processWPUser($litterNotInSystem->uzivatel, User::class);
        $litterInSystem->breeder_name = Pp_informace_orig::getBreederName($litterNotInSystem->id);
        $litterInSystem->breeder_contact = Pp_informace_orig::getBreederContact($litterNotInSystem->id);

        $litterInSystem->saveOrFail();

        // Update litter's registration
        self::updateLitterRegistration($litterNotInSystem, $litterIdInSystem);

        // Update litter's animals
        self::updateLitterAnimals($litterNotInSystem, $litterIdInSystem);
    }

    // Update litter's registration
    private static function updateLitterRegistration($litterNotInSystem, $litterIdInSystem) {
        $litterApprovalRequestsForLitterNotInSystem = Pp_zadosti::query()->where('id_pp', $litterNotInSystem->id)->get();

        // There is nothing to update
        if ($litterApprovalRequestsForLitterNotInSystem->isEmpty()) {
            return;
        }

        // Delete all existing records
        LitterApprovalRequest::query()->where('litter_id', $litterIdInSystem)->delete();

        foreach ($litterApprovalRequestsForLitterNotInSystem as $litterApprovalRequest) {
            $newLitterApprovalRequest = new LitterApprovalRequest();
            $newLitterApprovalRequest->created_at = Pp_zadosti::convertCreatedAt($litterApprovalRequest);
            $newLitterApprovalRequest->creator_id = Auth::id();
            $newLitterApprovalRequest->litter_id = $litterIdInSystem;
            $newLitterApprovalRequest->state = Pp_zadosti::convertState($litterApprovalRequest);
            $newLitterApprovalRequest->creator_note = Pp_zadosti::convertBreederNote($litterApprovalRequest);

            if ($litterApprovalRequest->stav === 'SCHVALENO' || $litterApprovalRequest->stav === 'ZAMITNUTO') {
                $newLitterApprovalRequest->updated_at = Pp_zadosti::convertUpdatedAt($litterApprovalRequest);
                $newLitterApprovalRequest->registrator_id = Auth::id();
                $newLitterApprovalRequest->registrator_note = Pp_zadosti::convertRegistratorNote($litterApprovalRequest);
            }

            if ($litterApprovalRequest->stav === 'SCHVALENO') {
                $newLitterApprovalRequest->registration_number = Pp_zadosti::convertRegistrationNumber($litterApprovalRequest);
                $newLitterApprovalRequest->registration_date = str_replace(' ', '', $litterNotInSystem->reg_dat_schvaleni);
            }

            $newLitterApprovalRequest->saveOrFail();
        }
    }

    // Add or update all animals belonging to the particular litter
    private static function updateLitterAnimals($litterNotInSystem, $litterIdInSystem) {
        $animalsInSystem = Animal::query()->where('litter_id', $litterIdInSystem)->get();

        $updatedAnimalsInSystem = [];

        foreach ($animalsInSystem as $animalInSystem) {
            $animalNotInSystem = Pp_miminka::query()->where('mimi_jmeno', $animalInSystem->name);

            // This means, we have animal in both tables (animals and pp_miminka)
            if ($animalNotInSystem->exists()) {

                $animalNotInSystem = $animalNotInSystem->first();

                $animalInSystem->breeder_id = Pp_miminka::processBreeder($animalNotInSystem);
                $animalInSystem->ear_type = Pp_miminka::convertEarType($animalNotInSystem);
                $animalInSystem->fur_type = Pp_miminka::convertFurType($animalNotInSystem);
                $animalInSystem->fur_color = Pp_miminka::convertFurColor($animalNotInSystem);
                $animalInSystem->markings = Pp_miminka::convertMarkings($animalNotInSystem);
                $animalInSystem->eyes_color = Pp_miminka::convertEyesColor($animalNotInSystem);
                $animalInSystem->owner_name = Pp_miminka_orig::getOwnerName($animalNotInSystem->id);
                $animalInSystem->owner_contact = Pp_miminka_orig::getOwnerContact($animalNotInSystem->id);
                $animalInSystem->owner_member_card_number = Pp_miminka_orig::getOwnerMemberCardNumber($animalNotInSystem->id);
                $animalInSystem->breeding_available = Pp_miminka::convertBreedingAvailable($animalNotInSystem);
                $animalInSystem->breeding_limitation = Pp_miminka::convertBreedingLimitation($animalNotInSystem);

                if (empty($animalInSystem->owner_id)) {
                    $animalInSystem->owner_id = Pp_miminka::processOwner($animalNotInSystem);
                }

                $animalInSystem->saveOrFail();

                // Push into the array so we know that this animal has been updated
                $updatedAnimalsInSystem[] = $animalInSystem->name;
            }
        }

        $animalsNotInSystem = Pp_miminka::query()->where('id_pp', $litterNotInSystem->id)->get();

        foreach ($animalsNotInSystem as $animalNotInSystem) {
            if (empty($animalNotInSystem->mimi_jmeno)) {
                continue;
            }

            $animalInSystem = Animal::query()->where('name', $animalNotInSystem->mimi_jmeno);

            // Animal from pp_miminka is already in system
            if ($animalInSystem->exists()) {
                $animalInSystem = $animalInSystem->first();

                // If we have already process this animal in previous loop,
                // do not process it again, because it has already been updated
                if (array_key_exists($animalNotInSystem->mimi_jmeno, $updatedAnimalsInSystem)) {
                    continue;
                }

                $animalInSystem->breeder_id = Pp_miminka::processBreeder($animalNotInSystem);
                $animalInSystem->ear_type = Pp_miminka::convertEarType($animalNotInSystem);
                $animalInSystem->fur_type = Pp_miminka::convertFurType($animalNotInSystem);
                $animalInSystem->fur_color = Pp_miminka::convertFurColor($animalNotInSystem);
                $animalInSystem->markings = Pp_miminka::convertMarkings($animalNotInSystem);
                $animalInSystem->eyes_color = Pp_miminka::convertEyesColor($animalNotInSystem);
                $animalInSystem->owner_name = Pp_miminka_orig::getOwnerName($animalNotInSystem->id);
                $animalInSystem->owner_contact = Pp_miminka_orig::getOwnerContact($animalNotInSystem->id);
                $animalInSystem->owner_member_card_number = Pp_miminka_orig::getOwnerMemberCardNumber($animalNotInSystem->id);
                $animalInSystem->breeding_available = Pp_miminka::convertBreedingAvailable($animalNotInSystem);
                $animalInSystem->breeding_limitation = Pp_miminka::convertBreedingLimitation($animalNotInSystem);

                if (empty($animalInSystem->owner_id)) {
                    $animalInSystem->owner_id = Pp_miminka::processOwner($animalNotInSystem);
                }

                $animalInSystem->saveOrFail();

                continue;
            }

            // Animal from pp_miminka has not yet been added to the animals table,
            // so add it
            $newAnimal = new Animal();
            $newAnimal->creator_id = Auth::id();
            $newAnimal->breeder_id = Pp_miminka::processBreeder($animalNotInSystem);
            $newAnimal->owner_id = Pp_miminka::processOwner($animalNotInSystem);
            $newAnimal->mother_id = Pp_miminka::processMother($litterIdInSystem);
            $newAnimal->father_id = Pp_miminka::processFather($litterIdInSystem);
            $newAnimal->litter_id = $litterIdInSystem;
            $newAnimal->name = Pp_miminka::convertName($animalNotInSystem);
            $newAnimal->sex = Pp_miminka::convertSex($animalNotInSystem);
            $newAnimal->eyes_color = Pp_miminka::convertEyesColor($animalNotInSystem);
            $newAnimal->ear_type = Pp_miminka::convertEarType($animalNotInSystem);
            $newAnimal->fur_color = Pp_miminka::convertFurColor($animalNotInSystem);
            $newAnimal->fur_type = Pp_miminka::convertFurType($animalNotInSystem);
            $newAnimal->markings = Pp_miminka::convertMarkings($animalNotInSystem);
            $newAnimal->birthdate = Pp_miminka::convertBirthdate($litterIdInSystem);
            $newAnimal->owner_name = Pp_miminka_orig::getOwnerName($animalNotInSystem->id);
            $newAnimal->owner_contact = Pp_miminka_orig::getOwnerContact($animalNotInSystem->id);
            $newAnimal->owner_member_card_number = Pp_miminka_orig::getOwnerMemberCardNumber($animalNotInSystem->id);
            $newAnimal->breeding_available = Pp_miminka::convertBreedingAvailable($animalNotInSystem);
            $newAnimal->breeding_limitation = Pp_miminka::convertBreedingLimitation($animalNotInSystem);

            $newAnimal->saveOrFail();
        }
    }

    private static function addLitter($litter) {
        $newLitter = new Litter();
        $newLitter->id = $litter->id + self::LITTER_TABLE_OFFSET;
        $newLitter->creator_id = self::convertCreator($litter);
        $newLitter->owner_id = self::processOwner($litter);
        $newLitter->father_id = self::processFather($litter);
        $newLitter->mother_id = self::processMother($litter);
        $newLitter->birthdate = self::convertBirthdate($litter);
        $newLitter->label = self::convertLabel($litter);
        $newLitter->babies_born = self::convertBornBabies($litter);
        $newLitter->babies_reared = self::convertRearedBabies($litter);
        $newLitter->reared_boys = self::convertRearedBoys($litter);
        $newLitter->reared_girls = self::convertRearedGirls($litter);
        $newLitter->breeder_name = Pp_informace_orig::getBreederName($litter->id);
        $newLitter->breeder_contact = Pp_informace_orig::getBreederContact($litter->id);
        $newLitter->type = self::convertType($litter);

        $newLitter->saveOrFail();

        $newLitter->refresh();

        return $newLitter->id;
    }

    public static function import() {
        $litters = self::all();

        foreach ($litters as $litter) {
            $litterIdInSystem = null;

            // If litter could not be found, create it and assign animals and registration to it
            // Otherwise update the existing litter
            if (!self::isLitterDuplicate($litter, $litterIdInSystem)) {
                $litterIdInSystem = self::addLitter($litter);
                self::updateLitter($litter, $litterIdInSystem);
            } else {
                self::updateLitter($litter, $litterIdInSystem);
            }
        }
    }
}
