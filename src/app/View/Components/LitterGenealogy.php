<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class LitterGenealogy extends Component
{
    public $genealogy;
    public $litter;

    /**
     * Create a new component instance.
     * @param $genealogy
     * @param $litter
     */
    public function __construct($genealogy, $litter)
    {
        $this->genealogy = $genealogy;
        $this->litter = $litter;
    }

    /**
     * Return litter's registration number
     *
     * @return string
     */
    public function registrationNumber() {
        if (empty($this->litter)) {
            return '???';
        }

        $approvalRequest = $this->litter->latestApprovalRequest;

        if (empty($approvalRequest) || !$approvalRequest->exists() || $approvalRequest->state !== 'Approved') {
            return '???';
        }

        return $approvalRequest->registration_number;
    }

    public function varieties() {
        return $this->litter->varieties ?? '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('generated_docs.litter-genealogy');
    }
}
