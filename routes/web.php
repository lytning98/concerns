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

Route::get('/', [
    'as' => 'index',
    'uses' => 'IndexController@index',
]);

Route::any('/logout', [
    'as' => 'logout',
    'uses' => 'HomeController@logout',
]);

Route::any('/concern', [
    'as' => 'concern',
    'uses' => 'ConcernController@index',
]);

Route::any('/concern/add', [
   'as' => 'concern-add',
   'uses' => 'ConcernController@add'
]);

Route::any('/concern/modify/{id}', [
   'as' => 'concern-modify',
   'uses' => 'ConcernController@modify'
]);

Route::any('/concern/update', [
    'as' => 'concern-update',
    'uses' => 'ConcernController@update'
]);

Auth::routes();

Route::get('/home', 'HomeController@index');
