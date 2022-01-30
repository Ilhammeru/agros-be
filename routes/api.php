<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register', [AuthController::class, 'register'])->name('register.post');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api');

// customer
Route::middleware(['auth:api'])->group(function () {
    Route::get('profile', [UserController::class, 'show']);
    Route::get('general', [UserController::class, 'general']);
    Route::patch('update', [UserController::class, 'update']);
    Route::get('mitra', [UserController::class, 'index']);
    Route::post('delete', [UserController::class, 'delete']);
});
