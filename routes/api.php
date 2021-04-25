<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::post('login', 'Auth\LoginController@login')->name('login');

Route::middleware('auth:sanctum')->group(function() {
    Route::apiResource('projects', 'ProjectController');
    Route::put('projects/{project}/un-assign', 'ProjectController@unAssingn');
    Route::put('projects/{project}/order-columns', 'ProjectController@orderColumns');

    Route::apiResource('projects/{project}/columns', 'ColumnController');

    Route::apiResource('projects/{project}/tasks', 'TaskController');
    Route::post('projects/{project}/tasks/{id}/assign/{user}', 'TaskController@assign');
    Route::post('projects/{project}/tasks/{id}/unassign/{user}', 'TaskController@unassign');
});
