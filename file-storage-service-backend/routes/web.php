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

// v1
$router->group(['prefix' => 'api/v1'], function (Router $router) {
    // 分塊上傳
    $router->post('/file/chunk', 'FileController@chunkUploadFile');
    // 合併分塊
    $router->post('/file/chunk/merge', 'FileController@mergeChunks');
    // 取得檔案資訊
    $router->get('/file/info/{folder}/{filename}', 'FileController@getFileInformation');
    // 取得單一檔案
    $router->get('/file/{filename}', 'FileController@getSingleFile');
    // 多檔包 Zip 下載
    $router->post('/files/download', 'FileController@getMultipleFiles');
});
