<?php

namespace App\Http\Controllers;

use App\Animal;
use App\Litter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AnimalsController extends Controller
{
    /**
     * Show view with animal's template for .pdf export
     *
     * @param int $id
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function export(int $id) {
        $animal = Animal::getAnimalForPdf($id);

        // Check, whether user is authorized to view this page
        $this->authorize('canExportAnimal', $animal);

        $litter = null;
        $genealogy = null;

        // Is animal part of some litter?
        if (isset($animal->litter_id)) {
            $litter = Litter::getLitter($animal->litter_id);
            $genealogy = Animal::getGenealogy($litter, 0);
        }

        // If litter is not known, try to get animal's genealogy from animal's parents
        if (!$genealogy) {
            $genealogy = Animal::getGenealogy($animal, 0);
        }

        // Set animal's page orientation
        $orientation = 'portrait';

        if ($litter && $litter->type !== 'VP') {
            $orientation = 'landscape';
        }

        return view('generated_docs.pdf', [
            'animal' => $animal,
            'litter' => $litter,
            'genealogy' => $genealogy,
            'orientation' => $orientation
        ]);
    }
}
