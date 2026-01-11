<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\IndustryController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SalespersonController;
use App\Http\Controllers\Api\SalespersonProfileController;
use App\Http\Controllers\HealthController;
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

// Health Check routes (for load balancer and monitoring)
Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/detailed', [HealthController::class, 'detailed']);

// Swagger API Documentation (development/staging only)
Route::prefix('docs')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Api\SwaggerController::class, 'index']);
    Route::get('/openapi.json', [\App\Http\Controllers\Api\SwaggerController::class, 'json']);
});

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
    Route::post('/register-salesperson', [AuthController::class, 'registerSalesperson']);
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
    // Public search route
    Route::get('/search', [CompanyController::class, 'search']);

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

// Salesperson routes
Route::prefix('salesperson')->group(function (): void {
    // Public route
    Route::get('/status', [SalespersonController::class, 'status']);

    // Protected routes
    Route::middleware('jwt.auth')->group(function (): void {
        Route::post('/upgrade', [SalespersonController::class, 'upgrade']);
        Route::put('/profile', [SalespersonController::class, 'updateProfile']);

        // Profile alias (points to /profile/ endpoint)
        Route::get('/profile', [SalespersonProfileController::class, 'me']);

        // Approval status (aggregated query)
        Route::get('/approval-status', [SalespersonController::class, 'approvalStatus']);

        // Experiences CRUD
        Route::get('/experiences', [ExperienceController::class, 'index']);
        Route::post('/experiences', [ExperienceController::class, 'store']);
        Route::put('/experiences/{id}', [ExperienceController::class, 'update']);
        Route::delete('/experiences/{id}', [ExperienceController::class, 'destroy']);

        // Certifications CRUD
        Route::get('/certifications', [CertificationController::class, 'index']);
        Route::post('/certifications', [CertificationController::class, 'store']);
        Route::delete('/certifications/{id}', [CertificationController::class, 'destroy']);
    });
});

// Public salespeople search
Route::get('/salespeople', [SalespersonController::class, 'index']);

// Admin routes
Route::middleware(['jwt.auth', 'admin'])->prefix('admin')->group(function (): void {
    Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals']);

    // Salesperson application management
    Route::get('/salesperson-applications', [AdminController::class, 'salespersonApplications']);
    Route::post('/salesperson-applications/{id}/approve', [AdminController::class, 'approveSalesperson']);
    Route::post('/salesperson-applications/{id}/reject', [AdminController::class, 'rejectSalesperson']);
});
