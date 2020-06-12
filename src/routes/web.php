<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/animals/{id}/export', 'AnimalsController@export')->name('animals.id.export.get');

    Route::get('/import/contacts', 'ImportController@importContacts')->name('import.contacts.get');
    Route::get('/import/czkp_animals', 'ImportController@importCZKPAnimals')->name('import.czkp_animals.get');
    Route::get('/import/czkp_litters', 'ImportController@importCZKPLitters')->name('import.czkp_litters.get');
    Route::get('/import/czkp_animals/litters', 'ImportController@connectAnimalWithLitter')->name('import.czkp_animals.litters.get');
    Route::get('/import/czkp_babies', 'ImportController@importCZKPBabies')->name('import.czkp_babies.get');
    Route::get('/import/pp_information', 'ImportController@importPPInformation')->name('import.pp_information.get');
});

Route::group(['prefix' => 'api', 'middleware' => ['auth:sanctum']], function() {
    Route::get('/animals/earTypes', 'Api\AnimalsController@getEarTypes')->name('animals.eartypes.get');
    Route::get('/animals/eyeColors', 'Api\AnimalsController@getEyeColors')->name('animals.eyecolors.get');

    Route::get('/animals/filter', 'Api\AnimalsController@filter')->name('animals.filter.get');
    Route::get('/animals/search', 'Api\AnimalsController@search')->name('animals.search.get');

    Route::get('/animals/{id}/genealogy', 'Api\AnimalsController@getAnimalGenealogy')->name('animals.id.genealogy.get');
    Route::get('/animals/{id}/history', 'Api\AnimalsController@getAnimalHistory')->name('animals.id.history.get');
    Route::get('/animals/{id}/notes', 'Api\AnimalsController@getAnimalNotes')->name('animals.id.notes.get');
    Route::get('/animals/{id}/registrations', 'Api\AnimalsController@getAnimalRegistrations')->name('animals.id.registrations.get');
    Route::get('/animals/{id}/registrations/availableClubs', 'Api\AnimalsController@getAvailableClubsForRegistration')->name('animals.id.registrations.availableclubs.get');
    Route::put('/animals/{id}/restore', 'Api\AnimalsController@restoreAnimal')->name('animals.id.restore.put');

    Route::get('/animalregistrations/clubs', 'Api\AnimalRegistrationsController@getClubs')->name('animalregistrations.clubs.get');
    Route::get('/animalregistrations/types', 'Api\AnimalRegistrationsController@getTypes')->name('animalregistrations.types.get');

    Route::get('/litters/filter', 'Api\LittersController@filter')->name('litters.filter.get');
    Route::get('/litters/search', 'Api\LittersController@search')->name('litters.search.get');

    Route::get('/litters/{id}/animals', 'Api\LittersController@getLitterAnimals')->name('litters.id.animals.get');
    Route::get('/litters/{id}/genealogy', 'Api\LittersController@getLitterGenealogy')->name('litters.id.genealogy.get');
    Route::get('/litters/{id}/history', 'Api\LittersController@getLitterHistory')->name('litters.id.history.get');
    Route::get('/litters/{id}/notes', 'Api\LittersController@getLitterNotes')->name('litters.id.notes');
    Route::get('/litters/{id}/requests', 'Api\LittersController@getLitterApprovalRequests')->name('litters.id.requests.get');
    Route::put('/litters/{id}/restore', 'Api\LittersController@restoreLitter')->name('litters.id.restore.put');

    Route::get('/people/search', 'Api\PeopleController@search')->name('people.search.get');

    Route::get('/stations/search', 'Api\StationsController@search')->name('stations.search.get');

    Route::get('/users/getUserWithRolesAndPermissions', 'Api\UsersController@getUserWithRolesAndPermissions')->name('users.permissions.get');
    Route::get('/users/hasRightTo', 'Api\UsersController@hasRightTo')->name('users.hasrightto.get');

    Route::get('/users/{user}/roles', 'Api\UsersController@getUserWithRoles')->name('users.user.roles.get');
    Route::put('/users/{user}/roles', 'Api\UsersController@setUserRoles')->name('users.user.roles.put');

    Route::apiResource('animals', 'Api\AnimalsController');
    Route::apiResource('animalregistrations', 'Api\AnimalRegistrationsController');
    Route::apiResource('litterapprovalrequests', 'Api\LitterApprovalRequestsController');
    Route::apiResource('litters', 'Api\LittersController');
    Route::apiResource('notes', 'Api\NotesController');
    Route::apiResource('people', 'Api\PeopleController');
    Route::apiResource('stations', 'Api\StationsController');
    Route::apiResource('users', 'Api\UsersController');
});

Auth::routes(['register' => false]);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/', 'HomeController@index')->name('home.get');
    Route::get('/{any}', 'HomeController@index')->where('any', '.*');
});
