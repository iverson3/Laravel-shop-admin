<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('users', UserController::class);

    $router->resource('movies', MovieController::class);

});




Route::group([
    'prefix'        => 'api',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.api-middleware'),
], function (Router $router) {

    $router->post('/editor/upload/picture', 'ApiController@editor_upload_pic');
    // $router->post('/editor/upload/picture', 'UploadController@postUploadImg');

});
