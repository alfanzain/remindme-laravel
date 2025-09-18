<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\ReminderController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('session')->middleware('api')->group(function () {
    Route::post('/', [SessionController::class, 'login']);
    Route::put('/', [SessionController::class, 'refresh']);
});

Route::prefix('reminders')->middleware('api')->group(function () {
    Route::get('/', [ReminderController::class, 'index']);
    Route::post('/', [ReminderController::class, 'store']);
    Route::get('/{id}', [ReminderController::class, 'show']);
    Route::put('/{id}', [ReminderController::class, 'update']);
    Route::delete('/{id}', [ReminderController::class, 'destroy']);
});