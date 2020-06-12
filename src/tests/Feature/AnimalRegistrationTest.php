<?php

namespace Tests\Feature;

use App\Animal;
use App\AnimalRegistration;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateAnimalRegistration() {
        // Create animal belonging to me
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Make new animal registration
        $myAnimalRegistration = factory(AnimalRegistration::class)->make([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to create a registration while being logged out
        $this->postJson(route('animalregistrations.store'), $myAnimalRegistration->toArray())
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        $myAnimalRegistration = $myAnimalRegistration->refresh();

        // Adding new animal registration should be successful
        $this->postJson(route('animalregistrations.store'), $myAnimalRegistration->toArray())
            ->assertStatus(201);

        // Make CZKP registration for this animal
        $myAnimalCZKPRegistration = factory(AnimalRegistration::class)->make([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'CZKP',
            'registration_number' => '123456',
            'year' => '2020',
            'type' => 'CZ',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to create CZKP registration when I don't have rights for it
        $this->postJson(route('animalregistrations.store'), $myAnimalCZKPRegistration->toArray())
            ->assertStatus(403);

        // Login as registrator
        $this->loginAsRegistrator();

        // Now I should be able to create CZKP registration
        $this->postJson(route('animalregistrations.store'), $myAnimalCZKPRegistration->toArray())
            ->assertStatus(201);

        // Create another CZKP registration
        $myAnimalCZKPRegistration->registration_number = '345678';

        // I should not be able to create another CZKP registration even when I am registrator
        $this->postJson(route('animalregistrations.store'), $myAnimalCZKPRegistration->toArray())
            ->assertStatus(403);

        // Login back as user
        $this->loginAsUser();

        // Create foreign user, his animal and animal registration
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        $foreignAnimalRegistration = factory(AnimalRegistration::class)->make([
            'animal_id' => $foreignAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // Creating registration for foreign animal should fail
        $this->postJson(route('animalregistrations.store'), $foreignAnimalRegistration->toArray())
            ->assertStatus(403);

        // Even setting myself as registrator won't help
        $foreignAnimalRegistration->registrator_id = $this->getUser()->id;

        $this->postJson(route('animalregistrations.store'), $foreignAnimalRegistration->toArray())
            ->assertStatus(403);

        $this->loginAsRegistrator();

        // I need to be registrator to create animal registration for foreign animals
        $this->postJson(route('animalregistrations.store'), $foreignAnimalRegistration->toArray())
            ->assertStatus(201);
    }

    public function testUpdateAnimalRegistration() {
        // Create animal belonging to me
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Create animal registration belonging to my animal
        $myAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to update registration unless I am logged in
        $this->putJson(route('animalregistrations.update', ['animalregistration' => $myAnimalRegistration->id]), $myAnimalRegistration->toArray())
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Now I should be able to update the registration
        $this->putJson(route('animalregistrations.update', ['animalregistration' => $myAnimalRegistration->id]), $myAnimalRegistration->toArray())
            ->assertStatus(204);

        $myAnimalRegistration->club = 'CZKP';
        $myAnimalRegistration->year = '2020';
        $myAnimalRegistration->type = 'CZ';

        // I should not be able to update the registration to CZKP type
        $this->putJson(route('animalregistrations.update', ['animalregistration' => $myAnimalRegistration->id]), $myAnimalRegistration->toArray())
            ->assertStatus(403);

        // Create foreign animal and its registration
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        $foreignAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $foreignAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        $foreignAnimalRegistration->registration_number = '2334567';

        // I should not be able to update registration for foreign animal
        $this->putJson(route('animalregistrations.update', ['animalregistration' => $foreignAnimalRegistration->id]), $foreignAnimalRegistration->toArray())
            ->assertStatus(403);

        $this->loginAsRegistrator();

        // Only when I have rights to do it
        $this->putJson(route('animalregistrations.update', ['animalregistration' => $foreignAnimalRegistration->id]), $foreignAnimalRegistration->toArray())
            ->assertStatus(204);
    }

    public function testGetAnimalRegistration() {
        // Create animal belonging to me
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Create animal registration belonging to my animal
        $myAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to get any registration while I am logged out
        $this->getJson(route('animalregistrations.show', ['animalregistration' => $myAnimalRegistration->id]))
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // I should get registration of my animal
        $this->getJson(route('animalregistrations.show', ['animalregistration' => $myAnimalRegistration->id]))
            ->assertStatus(200)
            ->assertJson($myAnimalRegistration->toArray());

        // Create foreign user, animal and registration belonging to that animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        $foreignAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $foreignAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should see registration of his animal
        $this->getJson(route('animalregistrations.show', ['animalregistration' => $foreignAnimalRegistration->id]))
            ->assertStatus(200)
            ->assertJson($foreignAnimalRegistration->toArray());
    }

    public function testDeleteAnimalRegistration() {
        // Create animal belonging to me
        $myAnimal = factory(Animal::class)->create([
            'creator_id' => $this->getUser()->id,
            'owner_id' => $this->getUser()->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        // Create animal registration belonging to my animal
        $myAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $this->getUser()->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to delete animal registration while being logged out
        $this->deleteJson(route('animalregistrations.destroy', ['animalregistration' => $myAnimalRegistration->id]))
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Delete animal registration
        $this->deleteJson(route('animalregistrations.destroy', ['animalregistration' => $myAnimalRegistration->id]))
            ->assertStatus(204);

        $foreignUser = $this->getRandomUserExcludingMe();

        // Create CZKP registration of my animal
        $myAnimalCZKPRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $myAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'CZKP',
            'registration_number' => '123456789',
            'year' => '2020',
            'type' => 'CZ',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to delete this registration
        $this->deleteJson(route('animalregistrations.destroy', ['animalregistration' => $myAnimalCZKPRegistration->id]))
            ->assertStatus(403);

        // Login as registrator
        $this->loginAsRegistrator();

        // However, registrator is able to delete CZKP registrations of foreign animals
        $this->deleteJson(route('animalregistrations.destroy', ['animalregistration' => $myAnimalCZKPRegistration->id]))
            ->assertStatus(204);

        // Log back as user
        $this->loginAsUser();

        // Create foreign animal with registration
        $foreignAnimal = factory(Animal::class)->create([
            'creator_id' => $foreignUser->id,
            'owner_id' => $foreignUser->people()->first()->id,
            'mother_id' => null,
            'father_id' => null,
            'litter_id' => null,
        ]);

        $foreignAnimalRegistration = factory(AnimalRegistration::class)->create([
            'animal_id' => $foreignAnimal->id,
            'registrator_id' => $foreignUser->id,
            'club' => 'Other',
            'registration_number' => '123456789',
            'breeding_limitation' => null,
            'breeding_available' => null,
        ]);

        // I should not be able to delete foreign animal's registration
        $this->deleteJson(route('animalregistrations.destroy', ['animalregistration' => $foreignAnimalRegistration->id]))
            ->assertStatus(403);
    }

    protected function setUp(): void
    {
        parent::setUp();

        factory(User::class,3)->create();
        factory(Station::class, 3)->create();
    }
}
