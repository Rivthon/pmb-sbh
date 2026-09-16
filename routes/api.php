<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PmbQueueController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('pmb/queue')
    ->name('api.pmb.queue.')
    ->controller(PmbQueueController::class)
    ->group(function () {
        Route::get('/me', 'me')->name('me');
        Route::post('/check-in', 'checkIn')->name('check-in');
        Route::get('/session/{session}/board', 'board')->name('board');
    });

Route::middleware(['throttle:120,1'])
    ->prefix('pmb/queue-public')
    ->name('api.pmb.queue-public.')
    ->controller(PmbQueueController::class)
    ->group(function () {
        Route::get('/session/{session}/board', 'board')->name('board');
    });
