<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FontController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StickerController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'verified'])->name('auth.logout');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
});

Route::prefix('email')->group(function () {
    Route::get('/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/change-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

Route::post('/forgot-password', [ResetPasswordController::class, 'sendEmail'])->name('password.send');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');

Route::prefix('templates')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/{template}', [TemplateController::class, 'show'])->name('templates.show');
});

Route::prefix('albums')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('/pricing', [AlbumController::class, 'getPricing'])->name('albums.pricing');
    Route::get('/{album}', [AlbumController::class, 'show'])->name('albums.show');
    Route::post('/', [AlbumController::class, 'store'])->name('albums.store');
    Route::put('/{album}', [AlbumController::class, 'update'])->name('albums.update');
    Route::post('/{album}/upload', [AlbumController::class, 'insertSticker'])->name('albums.sticker');
    Route::post('/{album}/delete', [AlbumController::class, 'deleteSticker'])->name('albums.sticker-delete');
    Route::delete('/{album}', [AlbumController::class, 'destroy'])->name('albums.delete');
});

Route::prefix('fonts')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [FontController::class, 'index'])->name('fonts.index');
});

Route::prefix('orders')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/admin', [OrderController::class, 'indexAdmin'])->middleware(['admin'])->name('orders.indexAdmin');
    Route::post('/{order}/download', [OrderController::class, 'downloadStickers'])->middleware(['admin'])->name('orders.downloadStickers');
    Route::post('/{album}', [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/{order}', [OrderController::class, 'changeStatus'])->name('orders.changeStatus');
});

Route::prefix('stickers')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/{album}', [StickerController::class, 'index'])->name('stickers.index');
    Route::get('/{album}/{position}', [StickerController::class, 'show'])->name('stickers.show');
});

Route::prefix('admin')->middleware((['auth:sanctum', 'verified', 'admin']))->group(function () {
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('admin.statistics');
    Route::get('/config', [AdminController::class, 'getConfig'])->name('admin.getconfig');
    Route::patch('/config', [AdminController::class, 'patchConfig'])->name('admin.patchconfig');
});