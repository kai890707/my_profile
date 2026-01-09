<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
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

// Public authentication routes
Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected authentication routes
Route::middleware('jwt.auth')->prefix('auth')->group(function (): void {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Example of role-protected route
Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function (): void {
    // Admin routes will go here
});

// Example of salesperson-protected route
Route::middleware(['jwt.auth', 'role:salesperson,admin'])->prefix('salesperson')->group(function (): void {
    // Salesperson routes will go here
});
