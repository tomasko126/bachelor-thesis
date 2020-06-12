<?php

namespace Tests;

use App\People;
use App\Station;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static $user = null;

    /**
     * Method intended for creating new user (typically myself, who is later signed in)
     * @return Collection|Model|mixed|null
     * @throws Throwable
     */
    private function setUser() {
        if (!self::$user) {
            $stationData = [
                'name' => 'User station',
                'creator_id' => 1,
            ];

            $personData = [
                'creator_id' => 1,
                'station_id' => 1,
                'user_id' => 1,
                'name' => 'Tomáš Taro',
                'email' => 'tarotoma@fit.cvut.cz',
                'telephone_number' => '123456789',
            ];

            $userData = [
                'name' => 'Tomáš Taro',
                'email' => 'tarotoma@fit.cvut.cz',
                'password' => '',
            ];

            User::unguard();
            $user = new User($userData);
            $user->saveOrFail();
            User::reguard();

            Station::unguard();
            $station = new Station($stationData);
            $station->saveOrFail();
            Station::reguard();

            People::unguard();
            $person = new People($personData);
            $person->saveOrFail();
            People::reguard();

            self::$user = $user;
        }
    }

    public function getUser() {
        return self::$user;
    }

    public function loginAsAdmin() {
        $user = $this->getUser();
        $user->syncRoles('admin');

        $this->be($user);
    }

    public function loginAsApprover() {
        $user = $this->getUser();
        $user->syncRoles('litters requests approver');

        $this->be($user);
    }

    public function loginAsRegistrator() {
        $user = $this->getUser();
        $user->syncRoles('registrator');

        $this->be($user);
    }

    public function loginAsUser() {
        $user = $this->getUser();
        $user->syncRoles('user');

        $this->be($user);
    }

    /**
     * Get random user from collection excluding myself
     * @return User[]|Collection|mixed
     * @throws Throwable
     */
    public function getRandomUserExcludingMe() {
        $users = User::all()->where('id', '!=', $this->getUser()->id);

        $user = $users->random();

        if (!$user->people()->exists()) {
            $person = new People();
            $person->creator_id = $this->getUser()->id;
            $person->user_id = $user->id;
            $person->name = $user->name;
            $person->email = $user->email;
            $person->saveOrFail();
        }

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::$user = null;
    }
}
