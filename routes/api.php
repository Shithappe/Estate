<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\booking_data;


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

Route::get('/getEstateAdmin', [BookController::class, 'getEstateAdmin']);
Route::post('/book', [BookController::class, 'storeOrUpdate']);
Route::post('/book/{id}', [BookController::class, 'storeOrUpdate']);
Route::delete('/book/{id}', [BookController::class, 'destroy']);

Route::post('/booking_data_rate', [booking_data::class, 'booking_data_rate']);
Route::get('/booking_data_map_card/{id}', [booking_data::class, 'booking_data_map_card']);

Route::get('/get_nearby_location', [booking_data::class, 'get_nearby_location']);

Route::post('/booking_data_filters', [booking_data::class, 'booking_data_filters']);