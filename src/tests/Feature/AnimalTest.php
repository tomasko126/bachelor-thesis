<?php

namespace Tests\Feature;

use App\Animal;
use App\AnimalRegistration;
use App\People;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testAnimalCreate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $animal = factory(Animal::class)->make([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // I should not be able to create new animal when not logged in
        $this->postJson(route('animals.store'), $animal->toArray())
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        $this->postJson(route('animals.store'), $animal->toArray())
            ->assertStatus(201);

        // Get foreign user
        $foreignUser = $this->getRandomUserExcludingMe();

        // Set foreign user as owner of my animal
        $animal->owner_id = $foreignUser->id;

        // Create animal with foreign owner - this should fail because only admin can set foreign owner
        $this->postJson(route('animals.store'), $animal->toArray())
            ->assertStatus(422);

        $this->loginAsAdmin();

        // Now we should succeed with creating animal with different owner
        $this->postJson(route('animals.store'), $animal->toArray())
            ->assertStatus(201);
    }

    public function testAnimalGet() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // I should not be getting any animal when logged out
        $this->getJson(route('animals.show', ['animal' => $myAnimal->id]))
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // I should see my animal
        $this->getJson(route('animals.show', ['animal' => $myAnimal->id]))
            ->assertStatus(200)
            ->assertJson($myAnimal->toArray());

        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // I should also see foreign animals
        $this->getJson(route('animals.show', ['animal' => $myAnimal->id]))
            ->assertStatus(200)
            ->assertJson($myAnimal->toArray());
    }

    public function testAnimalUpdate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my animal
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Update its data
        $editedAnimal = $myAnimal;
        $editedAnimal->name = $this->faker->name;

        // Updating animal while not logged in should result in 401
        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Now we should be successful with updating animal...
        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(204);

        // However, we should not be able to update the owner of the animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $editedAnimal->owner_id = $foreignUser->people()->first()->id;

        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(422);

        // Only admin is able to update owner
        $this->loginAsAdmin();

        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(204);

        // Log in back as user
        $this->loginAsUser();

        // Create Other registration to my animal
        $registration = factory(AnimalRegistration::class)->create([
            'animal_id' => $editedAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'Other'
        ]);

        // We should be successful with updating the animal even with other registration than under CZKP
        $editedAnimal->name = $this->faker->name;
        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(204);

        // Create CZKP registration for this animal
        $czkpRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $editedAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'CZKP'
        ]);

        // We should not be able to edit the animal, when it has CZKP registration
        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(403);

        // Only admin can update animal with CZKP registration
        $this->loginAsAdmin();

        $this->putJson(route('animals.update', ['animal' => $myAnimal->id]), $editedAnimal->toArray())
            ->assertStatus(204);

        // Log back as user
        $this->loginAsUser();

        // Create foreign animal
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Update foreign animal's data
        $foreignAnimal->name = $this->faker->name;

        // I should not be able to edit foreign animal
        $this->putJson(route('animals.update', ['animal' => $foreignAnimal->id]), $foreignAnimal->toArray())
            ->assertStatus(403);

        // Unless I have rights for it
        $this->loginAsApprover();

        $this->putJson(route('animals.update', ['animal' => $foreignAnimal->id]), $foreignAnimal->toArray())
            ->assertStatus(204);
    }

    public function testAnimalDelete() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my animal
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // I should not be allowed to delete an animal unless I log in
        $this->deleteJson(route('animals.destroy', ['animal' => $myAnimal->id]))
            ->assertStatus(401);

        $this->loginAsUser();

        // Now the deletion should be OK
        $this->deleteJson(route('animals.destroy', ['animal' => $myAnimal->id]))
            ->assertStatus(204);

        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // I should not be able to delete foreign animal
        $this->deleteJson(route('animals.destroy', ['animal' => $foreignAnimal->id]))
            ->assertStatus(403);

        // Unless I have rights to edit foreign animal
        $this->loginAsApprover();

        // Now we should be able to delete the animal
        $this->deleteJson(route('animals.destroy', ['animal' => $foreignAnimal->id]))
            ->assertStatus(204);
    }

    public function testAnimalsIndex() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my animals
        $myAnimals = factory(Animal::class, 30)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        $foreignUser = $this->getRandomUserExcludingMe();

        // Create foreign animals
        $foreignAnimals = factory(Animal::class, 30)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // We should not see animals when not logged in
        $this->getJson(route('animals.index'))
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Merge two arrays
        $allAnimals = array_merge($myAnimals->toArray(), $foreignAnimals->toArray());

        // We should be getting all animals
        $this->getJson(route('animals.index'))
            ->assertStatus(200)
            ->assertJson($allAnimals);
    }
}
