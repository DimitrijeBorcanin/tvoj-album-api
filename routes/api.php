<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
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
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
});

Route::prefix('email')->group(function () {
    Route::get('/verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::get('/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/change-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

Route::post('/forgot-password', [ResetPasswordController::class, 'sendEmail'])->name('password.send');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');

Route::prefix('templates')->group(function () {
    Route::get('/{template}', [TemplateController::class, 'show'])->name('templates.show');
});

Route::prefix('albums')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/', [AlbumController::class, 'index'])->name('templates.index');
    Route::get('/{album}', [AlbumController::class, 'show'])->name('templates.show');
});