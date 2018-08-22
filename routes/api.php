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
    /*
     * User*/
    Route::get('/userinfo', function (Request $request) {
        return $request->user()->myInfo;
    });
    Route::get('/user/bindEmail', 'Api\UserController@getEmailVerifyCode');
    Route::post('/user/bindEmail', 'Api\UserController@bindEmail');
    Route::put('/user', 'Api\UserController@update');

    /*
     * Upload*/
    Route::post('/upload', 'Api\UploadController@upload');

    /*
     * Shop*/
    Route::get('/shop', 'Api\ShopController@index');
    Route::post('/shop', 'Api\ShopController@add');
    Route::get('/shop/{id}', 'Api\ShopController@detail');
    Route::put('/shop/{id}', 'Api\ShopController@update');
    Route::delete('/shop/{id}', 'Api\ShopController@delete');
    /*
     * 商铺商品列表 */
    Route::get('/shop/{id}/goods', 'Api\GoodsController@index');
    Route::post('/shop/{id}/goods', 'Api\GoodsController@create');
    Route::get('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@detail');
    Route::put('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@update');
    Route::delete('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@delete');

    Route::get('/shop/certify', 'Api\ShopController@certificating');
    /* 提交审核 */
    Route::get('/shop/certify/{id}', 'Api\ShopController@certify');
    /* 取消审核 */
    Route::delete('/shop/certify/{id}', 'Api\ShopController@uncertify');
});



