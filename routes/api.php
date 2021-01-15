<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeatsStopsController;
use App\Http\Controllers\RegisterController;

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

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::get('/freaseats/{source}/{destination}', function ($src, $dst) {
        return (new SeatsStopsController)->freeSeats($src, $dst);
    });

    Route::post('/book/{userId}/{tripId}/{source}/{destination}/{seatId}', function($userId, $tripId, $src, $dst, $seatId) {
        return (new SeatsStopsController)->bookSeats($userId, $tripId, $src, $dst, $seatId);
    });
});