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

Route::get('/home', 'HomeController@index')->name('home');


Route::get('verify/{token}', ['as' => 'users.verify', 'uses' => 'UserController@verify']);

Route::get('demo/resetpass', ['as' => 'demo.resetpass', 'uses' => 'DemoController@resetpass']);
Route::get('demo/resurrect', ['as' => 'demo.resurrect', 'uses' => 'DemoController@resurrect']);


Auth::routes();

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});


