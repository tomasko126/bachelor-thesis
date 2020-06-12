<?php

namespace App\View\Components;

use DateTime;
use Exception;
use Illuminate\View\Component;
use Illuminate\View\View;

class AnimalOverview extends Component
{
    public $animal;
    public $litter;
    public $orientation;

    /**
     * Create a new component instance.
     *
     * @param $animal
     * @param $litter
     * @param $orientation
     */
    public function __construct($animal, $litter, $orientation)
    {
        $this->animal = $animal;
        $this->litter = $litter;
        $this->orientation = $orientation;
    }

    /**
     * Return station
     *
     * @return string
     */
    public function station() {
        if (empty($this->litter) || empty($this->litter->owner) || empty($this->litter->owner->station)) {
            return '';
        }

        return $this->litter->owner->station->name;
    }

    /**
     * Return latest approval request
     *
     * @return |null
     */
    public function litterApprovalRequest() {
        if (empty($this->litter) || empty($this->litter->latestApprovalRequest) || !$this->litter->latestApprovalRequest->exists() || $this->litter->latestApprovalRequest->state !== 'Approved') {
            return null;
        }

        return $this->litter->latestApprovalRequest;
    }

    /**
     * Return date, when litter approval request was created
     *
     * @return string|null
     * @throws Exception
     */
    public function litterApprovalRequestCreated() {
        $request = $this->litterApprovalRequest();

        if (!isset($request)) {
            return null;
        }

        return (new DateTime($request->created_at))->format('d.m.Y');
    }

    /**
     * Return date, when litter approval request was approved
     *
     * @return |null
     */
    public function litterApprovalRequestApproved() {
        $request = $this->litterApprovalRequest();

        if (!isset($request)) {
            return null;
        }

        return $request->registration_date;
    }

    /**
     * Return registration number of litter approval request
     *
     * @return |null
     */
    public function litterApprovalRequestRegistrationNumber() {
        $request = $this->litterApprovalRequest();

        if (!isset($request)) {
            return null;
        }

        return $request->registration_number;
    }

    /**
     * Return animal's czkp registration
     *
     * @return |null
     */
    public function animalRegistration() {
        if (!isset($this->animal->czkpRegistration) || !$this->animal->czkpRegistration->exists()) {
            return null;
        }

        return $this->animal->czkpRegistration;
    }

    /**
     * Return animal's CZKP registration's date
     *
     * @return string|null
     * @throws Exception
     */
    public function animalRegistrationDate() {
        $registration = $this->animalRegistration();

        if (!isset($registration)) {
            return null;
        }

        return (new DateTime($registration->created_at))->format('d.m.Y');
    }

    /**
     * Return animal's registration number
     *
     * @return string|null
     */
    public function animalRegistrationNumber() {
        $registration = $this->animalRegistration();

        if (!isset($registration)) {
            return null;
        }

        return $registration->type . ' ' . $registration->registration_number . '-' . $registration->year;
    }

    /**
     * Return animal's registration note by registrator
     *
     * @return |null
     */
    public function animalRegistrationNote() {
        $registration = $this->animalRegistration();

        if (!isset($registration)) {
            return null;
        }

        return $registration->breeding_limitation;
    }

    /**
     * Return name of animal's registrator
     *
     * @return |null
     */
    public function animalRegistrationRegistrator() {
        $registration = $this->animalRegistration();

        if (!isset($registration)) {
            return null;
        }

        return $registration->registrator->name;
    }

    /**
     * Is animal available for breeding?
     *
     * @return |null
     */
    public function isAnimalAvailableForBreeding() {
        return $this->animal->breeding_status;
    }

    /**
     * Does animal have any breeding limitation?
     *
     * @return |null
     */
    public function animalBreedingLimitation() {
        return $this->animal->breeding_limitation;
    }

    /**
     * Get document type
     *
     * @return string
     */
    public function documentType() {
        if (empty($this->litter)) {
            return 'Průkaz původu';
        }

        if ($this->litter->type === 'NV') {
            return 'Průkaz původu nulového standardu';
        } else if ($this->litter->type === 'VP') {
            return 'Výpis předků';
        }

        return 'Průkaz původu';
    }

    /**
     * Get name of registration box
     *
     * @return string
     */
    public function registrationHeaderType() {
        if (empty($this->litter)) {
            return '';
        }

        if ($this->litter->type === 'NV') {
            return 'v Nulovém Standardu';
        }

        return '';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('generated_docs.animal-overview');
    }
}
