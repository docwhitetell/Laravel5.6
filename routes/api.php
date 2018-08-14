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
Route::group(['middleware' => ['cors']],function (){
    Route::get('/smsCode', 'SMSRegisterCodesController@getSmsCode')->name('getSmsCode');
    Route::post('/register', 'SMSRegisterCodesController@register')->name('smsRegister');
    Route::post('/login', 'Api\AuthController@login');
});

Route::get('/performance', function () {
    $response = new \App\User();
    //$response = \swoole_version();
    return $response->getUser();
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->put('/user/{id}', 'Api\UserController@bindUserEmail');