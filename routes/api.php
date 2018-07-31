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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




// 用来测试 API Tester工具
Route::post('/test/file', function(Request $request) {
    return "success";
});
Route::get('parameters', function (Request $request) {
    return $request->all();
});
Route::middleware('auth:api')->get('/test/api', function (Request $request) {
    return response('response',200, []);
});