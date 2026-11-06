<?php

use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MenuController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// ---- Public ----
Route::get('/menu', [MenuController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

Route::get('/me', [AuthController::class, 'me']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// ---- Logged in ----
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---- Admin only ----
    Route::prefix('admin')->middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages/{contactMessage}/read', [MessageController::class, 'markRead']);
        Route::delete('/messages/{contactMessage}', [MessageController::class, 'destroy']);

        Route::get('/menu', [MenuItemController::class, 'index']);
        Route::post('/menu', [MenuItemController::class, 'store']);
        Route::put('/menu/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/menu/{menuItem}', [MenuItemController::class, 'destroy']);
    });
});
