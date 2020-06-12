<?php

namespace Tests\Feature;

use App\Station;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating station
     */
    public function testStationCreate()
    {
        factory(User::class)->create();
        $station = factory(Station::class)->make();

        // This should fail because we are not authenticated
        $this->postJson(route('stations.store'), $station->toArray())
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // Try to create station now
        $this->postJson(route('stations.store'), $station->toArray())
            ->assertStatus(201);
    }

    /**
     * Test getting stations
     */
    public function testStationIndex() {
        // Create models
        factory(Station::class, 5)->create();

        // We should not be able to get any station when not authenticated
        $this->getJson(route('stations.index'))
        ->assertStatus(401);

        // Auth as user
        $this->loginAsUser();

        // Retrieve all stations
        $stations = Station::all();

        // We should be getting those 5 stations created before
        $this->getJson(route('stations.index'))
        ->assertStatus(200)
        ->assertJson($stations->toArray());
    }
}
