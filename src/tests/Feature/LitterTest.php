<?php

namespace Tests\Feature;

use App\Animal;
use App\Litter;
use App\LitterApprovalRequest;
use App\People;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LitterTest extends TestCase
{
    use RefreshDatabase;

    public function testLitterCreate() {
        // Make my litter
        $myVPLitter = factory(Litter::class)->make([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should not be able to create new litter unless I am logged in
        $this->postJson(route('litters.store'), $myVPLitter->toArray())
            ->assertStatus(401);

        $this->loginAsUser();

        // Create litter belonging to me
        $this->postJson(route('litters.store'), $myVPLitter->toArray())
            ->assertStatus(201);

        // Create foreign user and litter belonging to that user
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignVPLitter = factory(Litter::class)->make([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $foreignUser->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should not be able to create litter belonging to someone else
        $this->postJson(route('litters.store'), $foreignVPLitter->toArray())
            ->assertStatus(422);

        // ... unless I am admin
        $this->loginAsAdmin();

        $this->postJson(route('litters.store'), $foreignVPLitter->toArray())
            ->assertStatus(201);
    }

    public function testLitterGet() {
        // Create my litter
        $myVPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should not see any litter unless I log in
        $this->getJson(route('litters.show', ['litter' => $myVPLitter->id]))
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // Now I should see my litter, when I am logged in
        $this->getJson(route('litters.show', ['litter' => $myVPLitter->id]))
            ->assertStatus(200)
            ->assertJson($myVPLitter->toArray());

        // Create additional litters belonging to me
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myNVLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'NV',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should see my own litters even when they are not registered
        $this->getJson(route('litters.show', ['litter' => $myPPLitter->id]))
            ->assertStatus(200)
            ->assertJson($myPPLitter->toArray());

        $this->getJson(route('litters.show', ['litter' => $myNVLitter->id]))
            ->assertStatus(200)
            ->assertJson($myNVLitter->toArray());

        // Create foreign litters
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignVPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignPPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignNVLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'NV',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should see foreign VP litters
        $this->getJson(route('litters.show', ['litter' => $foreignVPLitter->id]))
            ->assertStatus(200)
            ->assertJson($foreignVPLitter->toArray());

        // However, I should not get access to PP and NV litters unless they have been registered
        $this->getJson(route('litters.show', ['litter' => $foreignPPLitter->id]))
            ->assertStatus(403);

        $this->getJson(route('litters.show', ['litter' => $foreignNVLitter->id]))
            ->assertStatus(403);

        // I should see non-approved litters when I am approver
        $this->loginAsApprover();

        $this->getJson(route('litters.show', ['litter' => $foreignPPLitter->id]))
            ->assertStatus(200)
            ->assertJson($foreignPPLitter->toArray());

        $this->getJson(route('litters.show', ['litter' => $foreignNVLitter->id]))
            ->assertStatus(200)
            ->assertJson($foreignNVLitter->toArray());

        // Login back as user
        $this->loginAsUser();

        // Create non-approved litter request, which has been sent
        $foreignPPLitterRequest = factory(LitterApprovalRequest::class)->create([
            'creator_id' => $foreignUser->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $foreignPPLitter->id,
            'state' => 'Sent',
        ]);

        // I should not have access to foreign litter when its approval request has not been approved
        $this->getJson(route('litters.show', ['litter' => $foreignPPLitter->id]))
            ->assertStatus(403);

        // Update litter's approval request to rejected state
        $foreignPPLitterRequest->state = 'Rejected';
        $foreignPPLitterRequest->saveOrFail();

        // I still should not have access to the rejected litter
        $this->getJson(route('litters.show', ['litter' => $foreignPPLitter->id]))
            ->assertStatus(403);

        // Update litter's approval request to Approved state
        $foreignPPLitterRequest->state = 'Approved';
        $foreignPPLitterRequest->saveOrFail();

        // Now I should see the particular litter
        $this->getJson(route('litters.show', ['litter' => $foreignPPLitter->id]))
            ->assertStatus(200)
            ->assertJson($foreignPPLitter->toArray());
    }

    public function testLitterUpdate() {
        // Create my litter
        $myVPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myVPLitter->for_breeding = $myVPLitter->for_breeding + 1;

        // I should not be able to update any litter when logged out
        $this->putJson(route('litters.update', ['litter' => $myVPLitter->id]), $myVPLitter->toArray())
            ->assertStatus(401);

        $this->loginAsUser();

        // Now I should be able to update the litter
        $this->putJson(route('litters.update', ['litter' => $myVPLitter->id]), $myVPLitter->toArray())
            ->assertStatus(204);

        // Create PP litter and approval request
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignUser = $this->getRandomUserExcludingMe();
        $myPPLitterApprovalRequest = factory(LitterApprovalRequest::class)->create([
            'creator_id' => $this->getUser()->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $myPPLitter->id,
            'state' => 'Approved',
        ]);

        // I should not be able to update litter when it has already been approved
        $this->putJson(route('litters.update', ['litter' => $myPPLitter->id]), $myPPLitter->toArray())
            ->assertStatus(403);

        // Admin is the only one being able to update approved litter
        $this->loginAsAdmin();

        $this->putJson(route('litters.update', ['litter' => $myPPLitter->id]), $myPPLitter->toArray())
            ->assertStatus(204);

        // Login back as user
        $this->loginAsUser();

        // Change owner of litter to another ID
        $myPPLitter->owner_id = $foreignUser->id;

        // I should not be able to update owner's ID to the foreign user
        $this->putJson(route('litters.update', ['litter' => $myPPLitter->id]), $myPPLitter->toArray())
            ->assertStatus(403);

        // Revert the owner change
        $myPPLitter->owner_id = $this->getUser()->id;

        // Create foreign litter
        $foreignPPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignPPLitter->for_breeding = $foreignPPLitter->for_breeding + 1;

        // I should not be able to update foreign litter
        $this->putJson(route('litters.update', ['litter' => $foreignPPLitter->id]), $foreignPPLitter->toArray())
            ->assertStatus(403);

        // Login as approver
        $this->loginAsApprover();

        // As approver I am able to update foreign litter
        $this->putJson(route('litters.update', ['litter' => $foreignPPLitter->id]), $foreignPPLitter->toArray())
            ->assertStatus(204);
    }

    public function testLitterDelete() {
        // Create my litter
        $myVPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'VP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // Create additional litters belonging to me
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myNVLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'NV',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should not be able to delete any litter when logged out
        $this->deleteJson(route('litters.destroy', $myVPLitter->id))
            ->assertStatus(401);

        $this->loginAsUser();

        // Now I should be able to delete my VP litter
        $this->deleteJson(route('litters.destroy', $myVPLitter->id))
            ->assertStatus(204);

        // I should also be able to delete PP litter with no registration attached to it
        $this->deleteJson(route('litters.destroy', $myPPLitter->id))
            ->assertStatus(204);

        // I should also be able to delete any litter belonging to me when it has not been approved yet
        $foreignUser = $this->getRandomUserExcludingMe();
        $myNVLitterApprovalRequest = factory(LitterApprovalRequest::class)->create([
            'creator_id' => $this->getUser()->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $myNVLitter->id,
            'state' => 'Sent',
        ]);

        // Delete NV litter
        $this->deleteJson(route('litters.destroy', $myNVLitter->id))
            ->assertStatus(204);

        // Create new PP litter with approved litter request
        $myPPLitter = factory(Litter::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $myPPLitterApprovalRequest = factory(LitterApprovalRequest::class)->create([
            'creator_id' => $this->getUser()->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $myPPLitter->id,
            'state' => 'Approved',
        ]);

        // I should not be able to delete my own litter when it has already been approved
        $this->deleteJson(route('litters.destroy', $myPPLitter->id))
            ->assertStatus(403);

        // Create foreign litter
        $foreignPPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        // I should not be able to delete foreign litter
        $this->deleteJson(route('litters.destroy', $foreignPPLitter->id))
            ->assertStatus(403);

        // Only when I am approver
        $this->loginAsApprover();

        $this->deleteJson(route('litters.destroy', $foreignPPLitter->id))
            ->assertStatus(204);

        // Log back as user
        $this->loginAsUser();

        // Create one more foreign litter and litter request
        $foreignPPLitter = factory(Litter::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->id,
            'type' => 'PP',
            'breeder_name' => '**************',
            'breeder_contact' => '**************',
        ]);

        $foreignPPLitterApprovalRequest = factory(LitterApprovalRequest::class)->create([
            'creator_id' => $foreignUser->id,
            'registrator_id' => $foreignUser->id,
            'litter_id' => $foreignPPLitter->id,
            'state' => 'Approved',
        ]);

        // I should not be able to delete approved litter
        $this->deleteJson(route('litters.destroy', $foreignPPLitter->id))
            ->assertStatus(403);

        // Even when I am approver
        $this->loginAsApprover();

        $this->deleteJson(route('litters.destroy', $foreignPPLitter->id))
            ->assertStatus(403);

        // Only when I am admin
        $this->loginAsAdmin();

        $this->deleteJson(route('litters.destroy', $foreignPPLitter->id))
            ->assertStatus(204);
    }

    protected function setUp(): void
    {
        parent::setUp();
        factory(User::class, 3)->create();
        factory(Station::class, 3)->create();
        factory(People::class,3)->create();
        factory(Animal::class, 5)->create();
    }
}
