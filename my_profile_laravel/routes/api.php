<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\IndustryController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SalespersonProfileController;
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

// Public Reference Data routes
Route::prefix('industries')->group(function (): void {
    Route::get('/', [IndustryController::class, 'index']);
    Route::get('/{id}', [IndustryController::class, 'show']);
});

Route::prefix('regions')->group(function (): void {
    Route::get('/', [RegionController::class, 'index']);
    Route::get('/flat', [RegionController::class, 'flat']);
    Route::get('/{id}', [RegionController::class, 'show']);
    Route::get('/{id}/children', [RegionController::class, 'children']);
});

// Public authentication routes
Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Protected authentication routes
Route::middleware('jwt.auth')->prefix('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Company routes (specific routes before wildcards)
Route::prefix('companies')->group(function (): void {
    // Protected specific routes first
    Route::middleware('jwt.auth')->group(function (): void {
        Route::get('/my', [CompanyController::class, 'myCompanies']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/{id}', [CompanyController::class, 'update']);
        Route::delete('/{id}', [CompanyController::class, 'destroy']);
    });

    // Public routes after (wildcard last)
    Route::get('/', [CompanyController::class, 'index']);
    Route::get('/{id}', [CompanyController::class, 'show']);
});

// Public Salesperson Profile routes
Route::prefix('profiles')->group(function (): void {
    Route::get('/', [SalespersonProfileController::class, 'index']);
    Route::get('/{id}', [SalespersonProfileController::class, 'show']);
});

// Protected Salesperson Profile routes
Route::middleware('jwt.auth')->prefix('profile')->group(function (): void {
    Route::get('/', [SalespersonProfileController::class, 'me']);
    Route::post('/', [SalespersonProfileController::class, 'store']);
    Route::put('/', [SalespersonProfileController::class, 'update']);
    Route::delete('/', [SalespersonProfileController::class, 'destroy']);
});

// Admin routes
Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals']);
    Route::post('/companies/{id}/approve', [AdminController::class, 'approveCompany']);
    Route::post('/companies/{id}/reject', [AdminController::class, 'rejectCompany']);
    Route::post('/profiles/{id}/approve', [AdminController::class, 'approveProfile']);
    Route::post('/profiles/{id}/reject', [AdminController::class, 'rejectProfile']);
});
