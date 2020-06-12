<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FourthLevelTableCell extends Component
{
    public $animal;

    /**
     * Create a new component instance.
     *
     * @param $animal
     */
    public function __construct($animal)
    {
        $this->animal = $animal;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.litter_genealogy_table.fourth-level-table-cell');
    }
}
