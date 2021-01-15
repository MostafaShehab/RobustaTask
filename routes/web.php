<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SeatsStopsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', UserController::class);

Route::get('/freaseats/{source}/{destination}', function ($src, $dst) {
    return (new SeatsStopsController)->freeSeats($src, $dst);
});

Route::post('/book/{userId}/{tripId}/{source}/{destination}/{seatId}', function($userId, $tripId, $src, $dst, $seatId) {
    return (new SeatsStopsController)->bookSeats($userId, $tripId, $src, $dst, $seatId);
});