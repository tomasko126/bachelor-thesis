<?php

namespace App\Http\Controllers;

use App\ImportModels\ContactsImport;
use App\ImportModels\Czkp_mimi;
use App\ImportModels\Czkp_vrh;
use App\ImportModels\Czkp_zvire;
use App\ImportModels\Pp_informace;

// Note: While doing import of data, the default timeout of 30 secs
// was multiple times exceeded. Therefore, for the following controllers,
// the execution time has been set to unlimited.
class ImportController extends Controller
{
    // Import CZKP animals
    public function importCZKPAnimals() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        Czkp_zvire::import();

        return response()->json(null);
    }

    // Import CZKP litters
    public function importCZKPLitters() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        Czkp_vrh::import();

        return response()->json(null);
    }

    // Import all contacts
    public function importContacts() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        ContactsImport::importContacts();

        return response()->json(null);
    }

    // Connect animals with imported litters
    public function connectAnimalWithLitter() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        Czkp_zvire::connectWithLitters();

        return response()->json(null);
    }

    // Import babies
    public function importCZKPBabies() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        Czkp_mimi::import();

        return response()->json(null);
    }

    // Import litters, babies and litter approval requests from newer app
    public function importPPInformation() {
        // Set execution time to unlimited
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        Pp_informace::import();

        return response()->json(null);
    }
}
