<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test getting part
     */
    public function testUsersIndex() {
        // Create users
        factory(User::class, 10)->create();

        // Getting them unauthenticated should result in 401
        $this->getJson(route('users.index'))
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // Even user can't access users from users table
        $this->getJson(route('users.index'))
            ->assertStatus(403);

        // Login as admin
        $this->loginAsAdmin();

        // Now we should be able to get all users
        $users = User::all();

        $this->getJson(route('users.index'))
            ->assertStatus(200)
            ->assertJson($users->toArray());
    }

    public function testUserDelete() {
        // Create new user
        $user = factory(User::class)->create();

        // Deleting unauthenticated should result in 401
        $this->deleteJson(route('users.destroy', ['user' => $user->id]))
            ->assertStatus(401);

        // Login as user
        $this->loginAsUser();

        // Deleting as user should result in 403
        $this->deleteJson(route('users.destroy', ['user' => $user->id]))
            ->assertStatus(403);

        // Login as admin
        $this->loginAsAdmin();

        // Now the deletion should be alright
        $this->deleteJson(route('users.destroy', ['user' => $user->id]))
            ->assertStatus(204);
    }
}
