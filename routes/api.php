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
    $response = \App\User::all();
    return $response;
});

/*
 * 授权访问路由 */
Route::group(['middleware' => ['auth:api','cors']],function (){
    Route::get('/userinfo', function (Request $request) {
        return $request->user()->myInfo;
    });
    Route::get('/user/bindEmail', 'Api\UserController@getEmailVerifyCode');
    Route::post('/user/bindEmail', 'Api\UserController@bindEmail');
    Route::put('/user', 'Api\UserController@update');
    Route::post('/upload', 'Api\UploadController@upload');
    Route::post('/uploads', 'Api\UploadController@uploads');
});



