<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return abort(404);
});

Route::get('images/{folder}/{albumId}/{fileName}', [ImageController::class, 'show'])->middleware('token.param')->name('images');

// Route::get('orders/{order}/download', [OrderController::class, 'downloadStickers']);
