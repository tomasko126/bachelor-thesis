<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasRoles;

    protected $guarded = ['id', 'wordpress_id', 'email_verified_at', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function createdAnimals() {
        return $this->hasMany('App\Animal', 'creator_id');
    }

    public function registratedAnimals() {
        return $this->hasMany('App\AnimalRegistration', 'registrator_id');
    }

    public function registratedLitters() {
        return $this->hasMany('App\Litter', 'creator_id');
    }

    public function notes() {
        return $this->hasMany('App\Note', 'creator_id');
    }

    public function litterApprovalCreators() {
        return $this->hasMany('App\LitterApprovalRequest', 'creator_id');
    }

    public function litterApprovalRegistrators() {
        return $this->hasMany('App\LitterApprovalRequest', 'registrator_id');
    }

    public function createdStations() {
        return $this->hasMany('App\Station', 'creator_id');
    }

    public function createdPeople() {
        return $this->hasMany('App\People', 'creator_id');
    }

    public function people() {
        return $this->hasMany('App\People', 'user_id');
    }


    /**
     * Search users by name
     *
     * @param string $keyword
     * @param string $field
     * @param string $order
     * @return Builder[]|Collection
     */
    public static function search(string $keyword = '%%', string $field = 'id', string $order = 'desc') {
        return self::query()->where('name', 'like', $keyword)->orderBy($field, $order)->get();
    }

    /**
     * Get all user's roles
     *
     * @param User $user
     * @param $allRoles
     * @return mixed
     */
    public static function getUserWithRoles(User $user, $allRoles) {
        $userRoles = $user->roles;

        foreach ($allRoles as $systemRole) {
            $systemRole->enabled = false;
        }

        foreach ($allRoles as $systemRole) {
            foreach ($userRoles as $userRole) {
                if ($userRole->name === $systemRole->name) {
                    $systemRole->enabled = true;
                    break;
                }
            }
        }

        return $allRoles;
    }

    /**
     * Get all users with roles
     *
     * @return Builder[]|Collection
     */
    public static function getAllUsersWithRoles() {
        return self::with('roles')->get();
    }

    /**
     * Get user with roles and permissions
     *
     * @param int $id
     * @return User
     */
    public static function getUserWithRolesAndPermissions(int $id) {
        /** @var User $user */
        $user = self::query()->where('id', $id)->first();

        $roles = $user->roles()->get()->pluck('name')->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        $user->allRoles = $roles;
        $user->allPermissions = $permissions;

        return $user;
    }


    /**
     * Set roles to the particular user
     *
     * @param array $roles
     * @param User $user
     * @return bool
     */
    public static function setUserRoles(array $roles, User $user) {
        $rolesToSet = [];

        foreach ($roles as $role) {
            if ($role['enabled']) {
                $rolesToSet[] = $role['name'];
            }
        }

        // This will remove all roles and set roles in the |rolesToSet| array
        $user->syncRoles($rolesToSet);

        return true;
    }

    /**
     * Does user have right to |ability| the |model|?
     *
     * @param string $ability
     * @param string $model
     * @param int $modelId
     * @param bool $withTrashed
     * @return bool
     */
    public static function hasRightTo(string $ability, string $model, ?int $modelId, ?bool $withTrashed = false) {
        /** @var User $user */
        $user = User::find(Auth::id());

        $model = 'App\\' . $model;

        if ($modelId) {
            if ($withTrashed) {
                return $user->can($ability, $model::withTrashed()->find($modelId));
            }

            return $user->can($ability, $model::find($modelId));
        }

        return $user->can($ability, new $model);
    }

    public static function getValidationRules() {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
