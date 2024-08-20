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
Route::post('/booking_data-map', [booking_data::class, 'booking_data_map']);

Route::post('/update_booking', [booking_data::class, 'update_booking']);
Route::post('/change_images_order', [booking_data::class, 'change_images_order']);

Route::post('/for_extension', [booking_data::class, 'for_extension']);
Route::post('/form_submissions', [booking_data::class, 'form_submissions']);

Route::post('/create_list', [booking_data::class, 'create_list']);
Route::post('/add_to_list', [booking_data::class, 'add_to_list']);
Route::post('/get_list', [booking_data::class, 'get_list']);

Route::patch('/list/{list_id}', [booking_data::class, 'update_list']);
Route::delete('/list/{list_id}', [booking_data::class, 'delete_list']);