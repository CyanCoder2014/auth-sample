<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\API\v1\Auth\AuthController;
use App\Http\Controllers\API\v1\Auth\RegisterController;
use App\Http\Controllers\API\v1\CartController;
use App\Http\Controllers\API\v1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use Aimeos\MShop;
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



Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

//    Auth::routes(['verify' => true]);

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');
    Route::post('refresh',[AuthController::class, 'refresh'])->name('refresh');
    Route::post('me', [AuthController::class, 'me'])->name('me');
    Route::get('loginfailed', [AuthController::class, 'loginfailed'])->name('loginfailed');


    Route::post('register',[RegisterController::class, 'register'])->name('register');
});



Route::resource('agenda', AgendaController::class)->except('edit','create');
