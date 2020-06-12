<?php

namespace Tests\Feature;

use App\Animal;
use App\Litter;
use App\LitterApprovalRequest;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LitterApprovalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testLitterApprovalRequestCreate() {
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myPPLitterRequest = [
            'creator_id' => $this->getUser()->id,
            'litter_id' => $myPPLitter->id,
            'state' => 'Approved',
            'creator_note' => null,
        ];

        // I should not be able to create new litter request when I am logged out
        $this->postJson(route('litterapprovalrequests.store'), $myPPLitterRequest)
            ->assertStatus(401);

        $this->loginAsUser();

        // I still can't create a new litter request because its state is not set to 'Sent'
        $this->postJson(route('litterapprovalrequests.store'), $myPPLitterRequest)
            ->assertStatus(422);

        $myPPLitterRequest['state'] = 'Sent';

        // Now I am able to send new litter approval request
        $this->postJson(route('litterapprovalrequests.store'), $myPPLitterRequest)
            ->assertStatus(201);

        // I can't send another litter approval request when the last one has not been rejected
        $this->postJson(route('litterapprovalrequests.store'), $myPPLitterRequest)
            ->assertStatus(403);

        // Set the last litter approval request to 'Rejected'
        $latestLitterRequest = LitterApprovalRequest::query()->where('litter_id', $myPPLitter->id)->first();
        $latestLitterRequest->state = 'Rejected';
        $latestLitterRequest->saveOrFail();

        // Declare new litter request
        $myPPLitterRequest = [
            'creator_id' => $this->getUser()->id,
            'litter_id' => $myPPLitter->id,
            'creator_note' => null,
            'state' => 'Sent',
        ];

        // Now we are able to send another litter approval request
        $this->postJson(route('litterapprovalrequests.store'), $myPPLitterRequest)
            ->assertStatus(201);

        // Create foreign litter and litter approval request
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignPPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignPPLitterRequest = [
            'creator_id' => $this->getUser()->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $foreignPPLitter->id,
            'state' => 'Sent',
        ];

        // Creating litter approval request for foreign litter should fail
        $this->postJson(route('litterapprovalrequests.store'), $foreignPPLitterRequest)
            ->assertStatus(403);
    }

    public function testLitterApprovalRequestUpdate() {
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myPPLitterRequestData = [
            'creator_id' => $this->getUser()->id,
            'litter_id' => $myPPLitter->id,
            'state' => 'Sent',
            'creator_note' => null,
        ];

        // Create litter request
        // Unguard and guard so we can mass assign
        LitterApprovalRequest::unguard();

        $litterRequest = new LitterApprovalRequest($myPPLitterRequestData);
        $litterRequest->saveOrFail();
        $litterRequest->refresh();

        // Unset creator_note caused by model refresh action
        unset($litterRequest->creator_note);

        LitterApprovalRequest::reguard();

        // Add registration data and change the request to approved
        $litterRequest->registration_number = '123456';
        $litterRequest->state = 'Approved';
        $litterRequest->registrator_id = $this->getUser()->id;
        $litterRequest->registration_date = '18.05.2020';
        $litterRequest->litter_registrator_note = null; // needed to set null because this field must be present

        // No-one could update litter approval request when logged out
        $this->putJson(route('litterapprovalrequests.update', ['litterapprovalrequest' => $litterRequest->id]), $litterRequest->toArray())
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // User can't update his own litter approval request
        $this->putJson(route('litterapprovalrequests.update', ['litterapprovalrequest' => $litterRequest->id]), $litterRequest->toArray())
            ->assertStatus(403);

        // Login as approver
        $this->loginAsApprover();

        // Approver can update the litter request
        $this->putJson(route('litterapprovalrequests.update', ['litterapprovalrequest' => $litterRequest->id]), $litterRequest->toArray())
            ->assertStatus(204);
    }

    protected function setUp(): void
    {
        parent::setUp();
        factory(User::class)->create();
        factory(Animal::class, 5)->state('Female')->create();
        factory(Animal::class, 5)->state('Male')->create();
    }
}
