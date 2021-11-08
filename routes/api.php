<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
	'prefix' => 'v1'
], function () {
    Route::post('/register', [UserController::class, 'register'])->name('register');
    Route::post('/login', [UserController::class, 'login'])->name('login');
    Route::post('/resendotp', [UserController::class, 'resendOtp'])->name('resendotp');
    Route::post('/verifyotp', [UserController::class, 'verifyOtp'])->name('verifyotp');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/updateprofile', [UserController::class, 'updateProfile']);
        Route::get('/getprofile',[UserController::class,'getProfile']);
        Route::get('/userlist', [UserController::class, 'userList']);
    });
});