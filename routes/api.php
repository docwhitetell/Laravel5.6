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

    Route::put('/user', 'Api\UserController@update');
    Route::get('/user', 'Api\UserController@detail');
    Route::get('/user/bindEmail', 'Api\UserController@getEmailVerifyCode');
    Route::post('/user/bindEmail', 'Api\UserController@bindEmail');

    /*
     * Upload*/
    Route::post('/upload', 'Api\UploadController@upload');

    /*
     * Shop*/
    Route::get('/shop', 'Api\ShopController@index');
    Route::post('/shop', 'Api\ShopController@add');
    Route::put('/shop/{id}', 'Api\ShopController@update');
    Route::delete('/shop/{id}', 'Api\ShopController@delete');
    /*
     * 商铺商品列表 */
    Route::post('/shop/{id}/goods', 'Api\GoodsController@create');

    Route::put('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@update');
    Route::delete('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@delete');

    Route::get('/shop/certify', 'Api\ShopController@certificating');
    /* 提交审核 */
    Route::get('/shop/certify/{id}', 'Api\ShopController@certify');
    /* 取消审核 */
    Route::delete('/shop/certify/{id}', 'Api\ShopController@uncertify');


    /*
     * 购物车*/
    Route::get('/user/shopcar', 'Api\ShopCarController@index');
    Route::get('/user/shopcar/{shop_id}', 'Api\ShopCarController@detail');  // 查看用户关于指定 商铺的 购物车
    Route::post('/user/shopcar', 'Api\ShopCarController@add');
    Route::put('/user/shopcar', 'Api\ShopCarController@update');
    Route::delete('/user/shopcar/{shop_car_id}', 'Api\ShopCarController@delete');
});

/*
 * 非授权访问路由
 * */
Route::group(['middleware'=>['cors']],function (){
    Route::get('/shops', 'Api\ShopController@shops');

    Route::get('/shop/{id}', 'Api\ShopController@detail');

    Route::get('/shop/{id}/goods', 'Api\GoodsController@index');

    Route::get('/shop/{shop_id}/goods/{goods_id}', 'Api\GoodsController@detail');
});


