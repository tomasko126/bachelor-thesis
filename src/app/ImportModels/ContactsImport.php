<?php

namespace App\ImportModels;

use App\People;
use App\Station;

class ContactsImport extends AbstractImport
{
    public static function processStation(string $stationName) {
        $station = Station::query()->where('name', $stationName);

        if ($station->exists()) {
            $station = $station->first();

            return $station->id;
        }

        Station::createStation([
            'name' => $stationName
        ], $station);

        return $station->id;
    }

    private static function processContactsOne($owner, $contactInfo, $ownerMemberCard) {
        if (empty($owner)) {
            return;
        }

        $email = $telephone_number = $stationName = null;

        if (!empty($contactInfo)) {
            [$email, $telephone_number, $stationName] = explode(',', $contactInfo);
        }

        $stationId = null;

        if (!empty($stationName)) {
            $stationId = self::processStation($stationName);
        }

        $ownerInPeople = People::query()->where('name', $owner);

        if ($ownerInPeople->exists()) {
            $ownerInPeople = $ownerInPeople->first();

            if (empty($ownerInPeople->email) && $email) {
                $ownerInPeople->email = strtolower($email);
            }

            if (empty($ownerInPeople->telephone_number) && $telephone_number) {
                $ownerInPeople->telephone_number = $telephone_number;
            }

            if (empty($ownerInPeople->station_id) && $stationId) {
                $ownerInPeople->station_id = $stationId;
            }

            if (empty($ownerInPeople->member_card_number) && $ownerMemberCard) {
                $ownerInPeople->member_card_number = $ownerMemberCard;
            }

            $ownerInPeople->saveOrFail();

            return;
        }

        People::createPerson([
            'name' => $owner,
            'email' => $email,
            'telephone_number' => $telephone_number,
            'station_id' => $stationId,
        ], $person);
    }

    private static function processContactsTwo($stationName, $contactInfo) {
        $name = $email = $telephone_number = null;

        if (!empty($contactInfo)) {
            [$name, $email, $telephone_number] = explode(',', $contactInfo);
        }

        $stationId = null;

        if (!empty($stationName)) {
            $stationId = self::processStation($stationName);
        }

        // Do not process further, when we do not know breeder's name
        if (empty($name)) {
            return;
        }

        $breederInPeople = People::query()->where('name', $name);

        if ($breederInPeople->exists()) {
            $breederInPeople = $breederInPeople->first();

            if (empty($breederInPeople->email) && $email) {
                $breederInPeople->email = strtolower($email);
            }

            if (empty($breederInPeople->telephone_number) && $telephone_number) {
                $breederInPeople->telephone_number = $telephone_number;
            }

            if (empty($breederInPeople->station_id) && $stationId) {
                $breederInPeople->station_id = $stationId;
            }

            $breederInPeople->saveOrFail();

            return;
        }

        People::createPerson([
            'name' => $name,
            'email' => $email,
            'telephone_number' => $telephone_number,
            'station_id' => $stationId,
        ], $person);

    }

    private static function processContactsThree($animal) {
        self::processPeople($animal->chovatel, $animal->chov_stanice);
        self::processPeople($animal->majitel, $animal->majet_stanice);
    }

    // Process animal's breeder and owner
    private static function processPeople(?string $personName, ?string $stationName) {
        // Create station if it has not been created yet
        $stationId = null;

        if (!empty($stationName)) {
            $stationId = self::processStation($stationName);
        }

        // Create person if it does not exist
        if (empty($personName)) {
            return null;
        }

        $person = People::query()->where('name', $personName);

        if (!$person->exists()) {
            People::createPerson(
                [
                    'station_id' => $stationId,
                    'name' => $personName,
                ],
                $person
            );

            return $person->id;
        }

        $person = $person->first();

        if (empty($person->station_id) && $stationId) {
            $person->station_id = $stationId;
            $person->saveOrFail();
        }

        return $person->id;
    }

    // Import all contacts from czkp_zvire, czkp_vrh, pp_miminka and pp_informace tables
    // We do not create any system user, because we do not know,
    // which animal belongs to which system user in all tables
    // WP users in all tables are handled separately by their respective Model classes
    public static function importContacts() {
        $pp_miminka = Pp_miminka::all();

        foreach ($pp_miminka as $baby) {
            self::processContactsOne($baby->mimi_majitel, $baby->mimi_maj_kontakt, $baby->mimi_maj_prukaz);
        }

        $pp_informace = Pp_informace::all();

        foreach ($pp_informace as $litter) {
            self::processContactsOne($litter->chovatel, $litter->chov_kontakt, null);
        }

        $czkp_vrh = Czkp_vrh::all();

        foreach ($czkp_vrh as $litter) {
            self::processContactsTwo($litter->chov_stanice, $litter->chov_kontakt);
        }

        $czkp_zvire = Czkp_zvire::all();

        foreach ($czkp_zvire as $animal) {
            self::processContactsThree($animal);
        }

        // Replace empty email and telephone_number values with null
        People::query()->where('email', '')->update(['email' => null]);
        People::query()->where('telephone_number', '')->update(['telephone_number' => null]);
    }
}
