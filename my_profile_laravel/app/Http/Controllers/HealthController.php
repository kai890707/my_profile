<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Health Check Controller
 *
 * Provides endpoints for monitoring application health
 */
final class HealthController extends Controller
{
    /**
     * Basic health check
     *
     * Returns 200 OK if application is running
     */
    public function index(): Response
    {
        return response('healthy', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Detailed health check
     *
     * Checks database, cache, and other dependencies
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $isHealthy = ! in_array(false, array_column($checks, 'status'), true);
        $statusCode = $isHealthy ? 200 : 503;

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $statusCode);
    }

    /**
     * Check application status
     *
     * @return array<string, mixed>
     */
    private function checkApp(): array
    {
        return [
            'status' => true,
            'message' => 'Application is running',
            'details' => [
                'env' => app()->environment(),
                'debug' => config('app.debug'),
                'version' => config('app.version', '1.0.0'),
            ],
        ];
    }

    /**
     * Check database connectivity
     *
     * @return array<string, mixed>
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $time = DB::selectOne('SELECT NOW() as time');

            return [
                'status' => true,
                'message' => 'Database connection successful',
                'details' => [
                    'driver' => config('database.default'),
                    'time' => $time->time ?? null,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity
     *
     * @return array<string, mixed>
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.time();
            $value = 'test';

            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved !== $value) {
                throw new \RuntimeException('Cache value mismatch');
            }

            return [
                'status' => true,
                'message' => 'Cache connection successful',
                'details' => [
                    'driver' => config('cache.default'),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Cache connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
