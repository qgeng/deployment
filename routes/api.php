<?php

use Illuminate\Http\Request;

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

Route::group(['prefix'=>'login'],function() {
    Route::post('login', 'Api\LoginController@login')->middleware('guest');
    Route::get('logout', 'Api\LoginController@logout')->middleware('guest');
});


Route::group(['prefix'=>'auth'],function() {
    Route::post('login', 'AuthController@login');

});

Route::group(['prefix'=>'swagger'],function() {
    Route::get('update', 'SwaggerController@update')->middleware('guest');
});

Route::group(['prefix'=>'base'],function() {
    Route::post('getAuthUser', 'Api\BaseController@getAuthUser')->middleware('admin');
});