<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Laravel\Lumen\Routing\Router;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// v1
$router->group(['prefix' => 'api/v1'], function (Router $router) {
    // 分塊上傳
    $router->post('/file/chunk', 'FileController@chunkUploadFile');
    // 合併分塊
    $router->post('/file/chunk/merge', 'FileController@mergeChunks');
});
