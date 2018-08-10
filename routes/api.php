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
    Route::get('/login', 'Api\Login@login');
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
