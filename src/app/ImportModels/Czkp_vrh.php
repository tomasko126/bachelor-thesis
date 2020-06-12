<?php

namespace App\ImportModels;

use App\Litter;
use App\LitterApprovalRequest;
use App\Note;
use App\People;
use App\User;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Czkp_vrh extends AbstractImport
{
    protected $table = 'czkp_vrh';

    private static function convertCreatedAt($litter) {
        return $litter->datum_zaznamu;
    }

    private static function convertFather($litter) {
        return $litter->otec;
    }

    private static function convertMother($litter) {
        return $litter->matka;
    }

    private static function convertLine($litter) {
        return !empty($litter->linie) ? $litter->linie : null;
    }

    private static function convertGeneticInformation($litter) {
        return !empty($litter->geny_vrhu) ? $litter->geny_vrhu : null;
    }

    private static function convertForBreedingAnimals($litter) {
        return !empty($litter->mlad_chovne) ? $litter->mlad_chovne : null;
    }

    private static function convertForPettingAnimals($litter) {
        return !empty($litter->mlad_pet) ? $litter->mlad_pet : null;
    }

    private static function convertBirthdate($litter) {
        if (empty($litter->datum_narozeni)) {
            return null;
        }

        return (new DateTime($litter->datum_narozeni))->format('d.m.Y');
    }

    private static function convertLabel($litter) {
        return !empty($litter->oznaceni) ? $litter->oznaceni : '';
    }

    private static function convertBabiesBorn($litter) {
        return !empty($litter->nar_mladat) ? $litter->nar_mladat : 0;
    }

    private static function convertBabiesReared($litter) {
        return !empty($litter->odchov_mladat) ? $litter->odchov_mladat : 0;
    }

    private static function convertRearedBoys($litter) {
        return !empty($litter->odch_kluci) ? $litter->odch_kluci : 0;
    }

    private static function convertRearedGirls($litter) {
        return !empty($litter->odch_holky) ? $litter->odch_holky : 0;
    }

    private static function convertBreederName($litter) {
        if (empty($litter->chov_kontakt)) {
            return null;
        }

        [$name, $email, $telephone_number] = explode(',', $litter->chov_kontakt);

        if (empty($name)) {
            return null;
        }

        return $name;
    }

    private static function convertBreederContact($litter) {
        if (empty($litter->chov_kontakt)) {
            return null;
        }

        [$name, $email, $telephone_number] = explode(',', $litter->chov_kontakt);

        if (empty($name)) {
            return null;
        }

        // Find breeder in people table, because we do not know,
        // whether parsed email and telephone_number has latest data
        $breederInPeople = People::query()->where('name', $name);

        // If breeder does not exist (which should not happen, because we've already imported all contacts in the previous step),
        // return null
        if (!$breederInPeople->exists()) {
            return null;
        }

        $contacts = [];

        $breeder = $breederInPeople->first();

        if (!empty($breeder->email)) {
            $contacts[] = $breeder->email;
        }

        if (!empty($breeder->telephone_number)) {
            $contacts[] = $breeder->telephone_number;
        }

        if (count($contacts) === 0) {
            return null;
        }

        return implode(', ', $contacts);
    }

    private static function convertType($litter) {
        return $litter->typ_priznani;
    }

    private static function processCreator($litter) {
        if (empty($litter->uzivatel)) {
            return Auth::id();
        }

        return self::processWPUser($litter->uzivatel, User::class);
    }

    private static function processBreeder($litter) {
        return self::processOwner($litter, User::class);
    }

    private static function processOwner($litter, $entity = People::class) {
        if (empty($litter->wp_id_majitel)) {
            return self::processCreator($litter);
        }

        return self::processWPUser($litter->wp_id_majitel, $entity);
    }

    private static function processRegistration($litter, $litterId) {
        // Only PP litters can have registration
        if ($litter->typ_priznani !== 'PP') {
            return null;
        }

        $litter->datum_registrace = str_replace(' ', '', $litter->datum_registrace);

        $registrationDate = (new DateTime($litter->datum_registrace))->format('d.m.Y');
        $registrationNumber = $litter->reg_cislo_vrhu . '-' . $litter->rok_reg;
        $timestamp = (new DateTime($litter->datum_registrace))->format('Y-m-d H:i:s');

        $registration = new LitterApprovalRequest();
        $registration->registration_number = $registrationNumber;
        $registration->created_at = $timestamp;
        $registration->updated_at = $timestamp;
        $registration->litter_id = $litterId;
        $registration->state = 'Approved';
        $registration->creator_id = Auth::id();
        $registration->registrator_id = Auth::id();
        $registration->registration_date = $registrationDate;

        $registration->saveOrFail();

        $registration->refresh();

        return $registration->id;
    }

    private static function processBreederNote($litter, $litterId) {
        if (empty($litter->poznamka_chov)) {
            return null;
        }

        $note = new Note();
        $note->creator_id = self::processBreeder($litter);
        $note->public = true;
        $note->note = $litter->poznamka_chov;
        $note->category = 'general';
        $note->litter_id = $litterId;

        $note->saveOrFail();
    }

    private static function processRegistratorNote($litter, $litterId) {
        if (empty($litter->poznamka_reg)) {
            return null;
        }

        $note = new Note();
        $note->creator_id = Auth::id();
        $note->public = true;
        $note->note = $litter->poznamka_reg;
        $note->category = 'general';
        $note->litter_id = $litterId;

        $note->saveOrFail();
    }

    private static function createLitter($litter) {
        if (Litter::find($litter->id_prvni_verze) !== null) {
            return $litter->id_prvni_verze;
        }

        $newLitter = new Litter();
        $newLitter->created_at = self::convertCreatedAt($litter);
        $newLitter->id = $litter->id_prvni_verze;
        $newLitter->father_id = self::convertFather($litter);
        $newLitter->mother_id = self::convertMother($litter);
        $newLitter->creator_id = self::processCreator($litter);
        $newLitter->owner_id = self::processOwner($litter);
        $newLitter->line = self::convertLine($litter);
        $newLitter->genetic_information = self::convertGeneticInformation($litter);
        $newLitter->for_breeding = self::convertForBreedingAnimals($litter);
        $newLitter->for_petting = self::convertForPettingAnimals($litter);
        $newLitter->birthdate = self::convertBirthdate($litter);
        $newLitter->label = self::convertLabel($litter);
        $newLitter->babies_born = self::convertBabiesBorn($litter);
        $newLitter->babies_reared = self::convertBabiesReared($litter);
        $newLitter->reared_boys = self::convertRearedBoys($litter);
        $newLitter->reared_girls = self::convertRearedGirls($litter);
        $newLitter->breeder_name = self::convertBreederName($litter);
        $newLitter->breeder_contact = self::convertBreederContact($litter);
        $newLitter->type = self::convertType($litter);

        $newLitter->saveOrFail();
        $newLitter->refresh();

        // Process litter's registration
        self::processRegistration($litter, $litter->id_prvni_verze);

        // Process litter's notes
        self::processBreederNote($litter, $litter->id_prvni_verze);
        self::processRegistratorNote($litter, $litter->id_prvni_verze);
    }

    // Import litters
    public static function import() {
        // Get latest version of every litter
        $littersIds = self::query()
            ->select([DB::raw('max(id) as id'), 'id_prvni_verze'])
            ->groupBy('id_prvni_verze');

        $littersIds = $littersIds->pluck('id_prvni_verze');

        $litters = self::query()->whereIn('id', $littersIds)->get();

        foreach ($litters as $litter) {
            self::createLitter($litter);
        }
    }
}
