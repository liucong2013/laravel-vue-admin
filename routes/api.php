<?php

use App\Http\Controllers\Code\CodeController;
use App\Http\Controllers\System\AuthorityController;
use App\Http\Controllers\System\MenuController;
use App\Http\Controllers\System\SysAccessLogErrorController;
use App\Http\Controllers\System\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::namespace('App\Http\Controllers\System')->group(function () {

    /** 菜单管理 */
    Route::group(['prefix' => 'menu', 'middleware' => ['auth.jwt']], function () {
        Route::get('/', [MenuController::class , 'all']);
        Route::get('/async', [MenuController::class , 'async']);
        Route::get('/find/{id}', [MenuController::class , 'find']);
        Route::get('/list', [MenuController::class , 'list']);
        Route::post('/', [MenuController::class , 'create']);
        Route::put('/{id}', [MenuController::class , 'update']);
        Route::delete('/{id}', [MenuController::class , 'destroy']);
    });

    /** 角色管理 */
    Route::group(['prefix' => 'authority', 'middleware' => ['auth.jwt']], function () {
        Route::get('/', [AuthorityController::class , 'all']);
        Route::get('/find/{id}', [AuthorityController::class , 'find']);
        Route::get('/list', [AuthorityController::class , 'list']);
        Route::post('/', [AuthorityController::class , 'create']);
        Route::put('/{id}', [AuthorityController::class , 'update']);
        Route::delete('/{id}', [AuthorityController::class , 'destroy']);
    });


    /** 用户管理 */
    Route::group(['prefix' => 'user'], function () {
        Route::post('register', 'UserController@register');
        Route::post('login', 'UserController@login');
        Route::group(['middleware' => ['auth.jwt']], function () {

            Route::put('setAuthority/{uuid}', 'UserController@setAuthority');
            Route::post('list', 'UserController@userList');
            Route::post('loginOut', 'UserController@loginOut');
            Route::post('/changePassword', 'UserController@changePassword');//修改管理员密码
            Route::delete('/{id}', 'UserController@destroy');
            //app用户管理

            Route::get('appUserList', [UserController::class , 'appUserList'] )->middleware(['all.list.before']);
            Route::put('appUser', 'UserController@editAppUser');
            Route::delete('appUser/{id}', 'UserController@delAppUser');
            Route::post('setAdmin', 'UserController@setAdmin');
            Route::post('setTemporaryPassword', 'UserController@setTemporaryPassword');
            Route::post('adminChangePassword', 'UserController@adminChangePassword');



        });
    });


    /** SysAccessLogError管理 */
    Route::group(['prefix' => 'sysAccessLogError', 'middleware' => ['auth.jwt']], function () {

        Route::get('/find/{id}', [SysAccessLogErrorController::class,'find']);
        Route::get('/list', [SysAccessLogErrorController::class,'list']);
        Route::delete('/{id}', [SysAccessLogErrorController::class,'destroy'] );
    });


    //二维码示例+excel导出
    Route::group(['prefix' => 'code', 'middleware' => ['auth.jwt']], function () {

        Route::get('/list', [CodeController::class , 'list']);
        Route::post('/', [CodeController::class , 'create']);
        Route::post('/exportExcel', [CodeController::class , 'exportExcel']);



    });



});


/** 测试用 */
Route::group(['prefix' => 'test'], function () {
    Route::get('/test', [\App\Http\Controllers\Common\TestController::class , 'test']);
});
