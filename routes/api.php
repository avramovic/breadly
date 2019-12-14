<?php

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

Route::get('/', function () {
    return 'name,'.json_encode(config('app.name')).',version,'.json_encode(config('app.version'));
});

Route::post('auth/login', ['as' => 'auth.login', 'uses' => 'Api\\AuthController@authenticate']);
Route::post('auth/guid', ['as' => 'auth.login.guid', 'uses' => 'Api\\AuthController@guidAuthenticate']);
Route::get('auth/profile', ['as' => 'auth.profile', 'uses' => 'Api\\AuthController@profile']);
Route::post('auth/profile', ['as' => 'auth.profile.update', 'uses' => 'Api\\AuthController@updateProfile']);
Route::post('auth/register', ['as' => 'auth.register', 'uses' => 'Api\\AuthController@register']);
Route::post('auth/password/forgot', ['as' => 'auth.password.forgot', 'uses' => 'Api\\AuthController@sendResetPasswordEmail']);
Route::post('auth/password/reset', ['as' => 'auth.password.reset', 'uses' => 'Api\\AuthController@resetPassword']);


Route::any('{table}/browse', ['as' => 'api.table.browse', 'uses' => 'Api\\BreadController@browse']);
Route::any('{table}/read/{id}', ['as' => 'api.table.read', 'uses' => 'Api\\BreadController@read']);
Route::post('{table}/edit/{id?}', ['as' => 'api.table.edit', 'uses' => 'Api\\BreadController@edit']);
Route::post('{table}/add', ['as' => 'api.table.add', 'uses' => 'Api\\BreadController@add']);
Route::post('{table}/delete/{id?}', ['as' => 'api.table.delete', 'uses' => 'Api\\BreadController@delete']);
Route::post('{table}/delete/force/{id?}', ['as' => 'api.table.delete.force', 'uses' => 'Api\\BreadController@forceDelete']);
Route::post('{table}/upload/{column}/{id}', ['as' => 'api.file.upload', 'uses' => 'Api\\BreadController@upload']);
