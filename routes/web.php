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

Route::get('/', 'MainController@actionIndex');
Route::get('/search', 'MainController@actionSearch');

Route::group(['prefix' => '/correlation', 'namespace' => 'Correlation'], function (){
    Route::get('/', 'CorrelationController@actionIndex');
    Route::get('/search', 'CorrelationController@actionSearch');
});