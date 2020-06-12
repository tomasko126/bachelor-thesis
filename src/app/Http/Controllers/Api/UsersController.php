<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', $request->user());

        $users = User::getAllUsersWithRoles();

        return response()->json($users);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $deleted = $user->delete();

        if (!$deleted) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }

    /**
     * Does user has right to access the particular page?
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function hasRightTo(Request $request) {
        $ability = $request->get('ability');
        $model = $request->get('model');
        $modelId = $request->get('model_id');
        $deleted = $request->get('deleted');

        $hasRight = User::hasRightTo($ability, $model, $modelId, $deleted);

        return response()->json($hasRight);
    }

    /**
     * Get all available user roles
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function getUserWithRoles(Request $request, User $user) {
        // Allow only admin to retrieve another user's roles
        if (!$request->user()->hasRole('admin')) {
            return response()->json(null, 403);
        }

        $roles = User::getUserWithRoles($user, Role::all());

        return response()->json($roles);
    }

    /**
     * Get roles and permissions of currently logged-in user
     *
     * @return JsonResponse
     */
    public function getUserWithRolesAndPermissions() {
        $user = User::getUserWithRolesAndPermissions(Auth::id());

        return response()->json($user);
    }

    /**
     * Set roles for user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function setUserRoles(Request $request, User $user) {
        // Allow only admin to set another user's roles
        if (!$request->user()->hasRole('admin')) {
            return response()->json(null, 403);
        }

        $updated = User::setUserRoles($request->all(), $user);

        if (!$updated) {
            return response()->json(null, 500);
        }

        return response()->json(null, 204);
    }
}
