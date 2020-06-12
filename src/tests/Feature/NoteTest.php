<?php

namespace Tests\Feature;

use App\Animal;
use App\Litter;
use App\Note;
use App\People;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Test creating note for animals
    public function testAnimalNoteCreate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Make sure, that I am the owner of the animal
        $myAnimal = factory(Animal::class)->create(['owner_id' => $this->getUser()->id]);

        // The note is belonging to my animal
        $note = factory(Note::class)->make(['animal_id' => $myAnimal->id, 'creator_id' => $this->getUser()->id, 'litter_id' => null]);

        // I should not be able to create note when I am not authenticated
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(401);

        $this->loginAsUser();

        // I should be able to create note for my animal
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(201);

        // Get random user excl. me
        $randomUser = $this->getRandomUserExcludingMe();

        // Create new animal belonging to this user
        $foreignAnimal = factory(Animal::class)->create(['creator_id' => $randomUser->id, 'owner_id' => $randomUser->id]);

        // Create note by me belonging to the foreign animal
        $note = factory(Note::class)->make(['animal_id' => $foreignAnimal->id, 'creator_id' => $this->getUser()->id, 'litter_id' => null]);

        // I should not be allowed to create note for foreign animal when I don't have rights
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(403);

        // Give me registrator role
        $this->loginAsRegistrator();

        // Now I should be able to create note belonging to the foreign animal
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(201);
    }

    // Test creating note for litters
    public function testLitterNoteCreate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my litter
        $myLitter = factory(Litter::class)->create(['owner_id' => $this->getUser()->id]);

        // Create note belonging to my litter
        $note = factory(Note::class)->make(['litter_id' => $myLitter->id, 'creator_id' => $this->getUser()->id, 'animal_id' => null]);

        // I should not be able to create note when I am not authenticated
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // I should be able to create note for my litter
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(201);

        // Get random user excl. me
        $randomUser = $this->getRandomUserExcludingMe();

        // Create new litter belonging to this user
        $foreignLitter = factory(Litter::class)->create(['creator_id' => $randomUser->id, 'owner_id' => $randomUser->id]);

        // Create note by me belonging to the foreign animal
        $note = factory(Note::class)->make(['litter_id' => $foreignLitter->id, 'creator_id' => $this->getUser()->id, 'animal_id' => null]);

        // I should not be allowed to create note for foreign litter when I don't have rights
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(403);

        // Raise my rights
        $this->loginAsApprover();

        // Now I should be able to create note belonging to the foreign animal
        $this->postJson(route('notes.store'), $note->toArray())
            ->assertStatus(201);
    }

    public function testAnimalNoteGet() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my animal and my note belonging to that animal
        $myAnimal = factory(Animal::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['animal_id' => $myAnimal->id, 'creator_id' => $this->getUser()->id, 'litter_id' => null]);

        // I should not see my note until I log in
        $this->getJson(route('notes.show', ['note' => $myNote->id]))
            ->assertStatus(401);

        // Log in as user
        $this->loginAsUser();

        // I should see my note
        $this->getJson(route('notes.show', ['note' => $myNote->id]))
            ->assertStatus(200)
            ->assertJson($myNote->toArray());

        // Create foreign user, his animal and note belonging to that animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create(['owner_id' => $foreignUser->id]);
        $foreignPublicNote = factory(Note::class)->create(['animal_id' => $foreignAnimal->id, 'creator_id' => $foreignUser->id, 'litter_id' => null, 'public' => true]);
        $foreignPrivateNote = factory(Note::class)->create(['animal_id' => $foreignAnimal->id, 'creator_id' => $foreignUser->id, 'litter_id' => null, 'public' => false]);

        // I should be able to see his public note to that litter
        $this->getJson(route('notes.show', ['note' => $foreignPublicNote->id]))
            ->assertStatus(200);

        // I should not be able to see his private note to that litter
        $this->getJson(route('notes.show', ['note' => $foreignPrivateNote->id]))
            ->assertStatus(403);

        // Admin should be able to see foreign note
        $this->loginAsAdmin();

        $this->getJson(route('notes.show', ['note' => $foreignPrivateNote->id]))
            ->assertStatus(200);
    }

    public function testLitterNoteGet() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        // Create my litter and my note belonging to that litter
        $myLitter = factory(Litter::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['litter_id' => $myLitter->id, 'creator_id' => $this->getUser()->id, 'animal_id' => null]);

        // I should not see my note until I log in
        $this->getJson(route('notes.show', ['note' => $myNote->id]))
            ->assertStatus(401);

        // Log in as user
        $this->loginAsUser();

        // I should see my note
        $this->getJson(route('notes.show', ['note' => $myNote->id]))
            ->assertStatus(200)
            ->assertJson($myNote->toArray());

        // Create foreign user, his animal and note belonging to that animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignLitter = factory(Litter::class)->create(['owner_id' => $foreignUser->id]);
        $foreignPublicNote = factory(Note::class)->create(['litter_id' => $foreignLitter->id, 'creator_id' => $foreignUser->id, 'animal_id' => null, 'public' => true]);
        $foreignPrivateNote = factory(Note::class)->create(['litter_id' => $foreignLitter->id, 'creator_id' => $foreignUser->id, 'animal_id' => null, 'public' => false]);

        // I should be able to see his public note to that litter
        $this->getJson(route('notes.show', ['note' => $foreignPublicNote->id]))
            ->assertStatus(200);

        // I should not be able to see his private note to that litter
        $this->getJson(route('notes.show', ['note' => $foreignPrivateNote->id]))
            ->assertStatus(403);

        // Admin should be able to see foreign note
        $this->loginAsAdmin();

        $this->getJson(route('notes.show', ['note' => $foreignPrivateNote->id]))
            ->assertStatus(200);
    }

    public function testAnimalNoteUpdate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $myAnimal = factory(Animal::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['animal_id' => $myAnimal->id, 'creator_id' => $this->getUser()->id, 'litter_id' => null]);

        $updatedNote = $myNote;
        $updatedNote->note = $this->faker->text(200);

        // I should not be able to update note when I am not logged in
        $this->putJson(route('notes.update', ['note' => $myNote->id]), $updatedNote->toArray())
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Now I should be able to update my note
        $this->putJson(route('notes.update', ['note' => $myNote->id]), $updatedNote->toArray())
            ->assertStatus(204);

        // Create foreign user and animal plus note belonging to that animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create(['owner_id' => $foreignUser->id, 'creator_id' => $foreignUser->id]);
        $foreignNote = factory(Note::class)->create(['animal_id' => $foreignAnimal->id, 'creator_id' => $foreignUser->id, 'litter_id' => null]);

        // Update the note
        $updatedNote = $foreignNote;
        $updatedNote->note = $this->faker->text(200);

        // Updating foreign note should fail
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(403);

        // Raise my power
        $this->loginAsRegistrator();

        // Even as a registrator, I should not be able to update other users' notes
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(403);

        // Make me admin
        $this->loginAsAdmin();

        // Now I should be able to update the note
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(204);
    }

    public function testLitterNoteUpdate() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $myLitter = factory(Litter::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['animal_id' => null, 'creator_id' => $this->getUser()->id, 'litter_id' => $myLitter->id]);

        $updatedNote = $myNote;
        $updatedNote->note = $this->faker->text(200);

        // I should not be able to update note when I am not logged in
        $this->putJson(route('notes.update', ['note' => $myNote->id]), $updatedNote->toArray())
            ->assertStatus(401);

        // Login
        $this->loginAsUser();

        // Login
        $this->loginAsUser();

        // Now I should be able to update my note
        $this->putJson(route('notes.update', ['note' => $myNote->id]), $updatedNote->toArray())
            ->assertStatus(204);

        // Create foreign user and animal plus note belonging to that animal
        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignLitter = factory(Litter::class)->create(['owner_id' => $foreignUser->id, 'creator_id' => $foreignUser->id]);
        $foreignNote = factory(Note::class)->create(['animal_id' => null, 'creator_id' => $foreignUser->id, 'litter_id' => $foreignLitter->id]);

        // Update the note
        $updatedNote = $foreignNote;
        $updatedNote->note = $this->faker->text(200);

        // Updating foreign note should fail
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(403);

        // Raise my power
        $this->loginAsApprover();

        // Even as a registrator, I should not be able to update other users' notes
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(403);

        // Make me admin
        $this->loginAsAdmin();

        // Now I should be able to update the note
        $this->putJson(route('notes.update', ['note' => $foreignNote->id]), $updatedNote->toArray())
            ->assertStatus(204);
    }

    public function testAnimalNoteDelete() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $myAnimal = factory(Animal::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['animal_id' => $myAnimal->id, 'creator_id' => $this->getUser()->id, 'litter_id' => null]);

        // I should not be able to delete any note when unauthenticated
        $this->deleteJson(route('notes.destroy', ['note' => $myNote->id]))
            ->assertStatus(401);

        $this->loginAsUser();

        // Now I should be able to delete my own note
        $this->deleteJson(route('notes.destroy', ['note' => $myNote->id]))
            ->assertStatus(204);

        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignAnimal = factory(Animal::class)->create(['owner_id' => $foreignUser->id, 'creator_id' => $foreignUser->id]);
        $foreignNote = factory(Note::class)->create(['animal_id' => $foreignAnimal->id, 'creator_id' => $foreignUser->id, 'litter_id' => null]);

        // I should not be able to delete foreign note
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(403);

        // Raise my power
        $this->loginAsRegistrator();

        // Even as a registrator, I should not be able to delete other users' notes
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(403);

        // Login as admin
        $this->loginAsAdmin();

        // Now I should be able to delete other users' notes
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(204);
    }

    public function testLitterNoteDelete() {
        factory(User::class)->create();
        factory(Station::class)->create();
        factory(People::class)->create();

        $myLitter = factory(Litter::class)->create(['owner_id' => $this->getUser()->id]);
        $myNote = factory(Note::class)->create(['animal_id' => null, 'creator_id' => $this->getUser()->id, 'litter_id' => $myLitter->id]);

        // I should not be able to delete any note when unauthenticated
        $this->deleteJson(route('notes.destroy', ['note' => $myNote->id]))
            ->assertStatus(401);

        $this->loginAsUser();

        // Now I should be able to delete my own note
        $this->deleteJson(route('notes.destroy', ['note' => $myNote->id]))
            ->assertStatus(204);

        $foreignUser = $this->getRandomUserExcludingMe();
        $foreignLitter = factory(Litter::class)->create(['owner_id' => $foreignUser->id, 'creator_id' => $foreignUser->id]);
        $foreignNote = factory(Note::class)->create(['animal_id' => null, 'creator_id' => $foreignUser->id, 'litter_id' => $foreignLitter->id]);

        // I should not be able to delete foreign note
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(403);

        // Raise my power
        $this->loginAsRegistrator();

        // Even as a registrator, I should not be able to delete other users' notes
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(403);

        // Login as admin
        $this->loginAsAdmin();

        // Now I should be able to delete other users' notes
        $this->deleteJson(route('notes.destroy', ['note' => $foreignNote->id]))
            ->assertStatus(204);
    }
}
