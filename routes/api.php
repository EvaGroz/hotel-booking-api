<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\HotelController;
Route::get('/hotels', [HotelController::class, 'index']);
Route::get('/hotels/{hotel}', [HotelController::class, 'show']);



use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;

Route::get('/rooms/{room}/availability', [RoomController::class, 'availability']);

Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{booking}', [BookingController::class, 'show']);
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);