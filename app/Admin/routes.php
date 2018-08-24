<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/front/users', 'UserController');
    $router->resource('/shop/manager', 'ShopManagerController');
    $router->resource('/shop/auditing', 'ShopAuditController');
    $router->post('/shop/auditing/approved', 'ShopAuditController@approved');
});
