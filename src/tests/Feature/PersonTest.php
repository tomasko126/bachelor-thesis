<?php

namespace Tests\Feature;

use App\People;
use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating person
     */
    public function testPersonCreate() {
        // Make sure, that there are some users and stations stored
        factory(User::class, 5)->create();
        factory(Station::class, 5)->create();

        $person = factory(People::class)->make();

        $this->postJson(route('people.store'), $person->toArray())
            ->assertStatus(401);

        $this->loginAsUser();

        $this->postJson(route('people.store'), $person->toArray())
            ->assertStatus(201);
    }

    /**
     * Test getting people
     */
    public function testPersonIndex() {
        // Make sure, that there are some users and stations stored
        factory(User::class, 5)->create();
        factory(Station::class, 5)->create();

        factory(People::class, 10)->create();

        $this->getJson(route('people.index'))
            ->assertStatus(401);

        $this->loginAsUser();

        $people = People::all();

        $this->getJson(route('people.index'))
            ->assertStatus(200)
            ->assertJson($people->toArray());
    }
}
