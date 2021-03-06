<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('home', 'HomeController@index')->name('home');
Route::get('verify/{token}', ['as' => 'users.verify', 'uses' => 'UserController@verify']);
Auth::routes();

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::post('tinywebdb/purge', ['as' => 'admin.tinywebdb.purge', 'uses' => 'UserController@verify']);
});

Route::post('getvalue', ['as' => 'tinywebdb.getvalue', 'uses' => 'TinyWebDbController@getvalue']);
Route::post('storeavalue', ['as' => 'tinywebdb.storeavalue', 'uses' => 'TinyWebDbController@storeavalue']);


