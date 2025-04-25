<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransformController;

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

Route::prefix('v1')->group(function () {
    // 认证相关路由
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);

        // 需要认证的路由
        Route::middleware('jwt.auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // 文案转换相关路由 - 需要认证
    Route::middleware('jwt.auth')->group(function () {
        // 转换类型
        Route::get('transform/types', [TransformController::class, 'getTypes']);

        // 执行转换
        Route::post('transform', [TransformController::class, 'transform']);

        // 历史记录
        Route::get('transform/history', [TransformController::class, 'getHistory']);
        Route::get('transform/history/{id}', [TransformController::class, 'getHistoryDetail']);
        Route::delete('transform/history/{id}', [TransformController::class, 'deleteHistory']);
        Route::delete('transform/history/batch', [TransformController::class, 'batchDeleteHistory']);
    });
});
