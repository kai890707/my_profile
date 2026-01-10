<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'YAMU 業務員推廣系統 API',
    description: '業務員管理與搜尋平台的 RESTful API - Laravel 11'
)]
#[OA\Server(
    url: 'http://localhost:8082/api',
    description: '開發環境'
)]
#[OA\Server(
    url: 'https://staging.yourdomain.com/api',
    description: 'Staging 環境'
)]
#[OA\Server(
    url: 'https://api.yourdomain.com/api',
    description: '生產環境'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: '輸入 JWT Token（不需要加 "Bearer " 前綴）'
)]
#[OA\Tag(
    name: '認證',
    description: 'Authentication endpoints - 用戶認證相關'
)]
#[OA\Tag(
    name: '業務員檔案',
    description: 'Salesperson Profile endpoints - 業務員個人檔案管理'
)]
#[OA\Tag(
    name: '公司管理',
    description: 'Company endpoints - 公司資訊管理'
)]
#[OA\Tag(
    name: '管理員',
    description: 'Admin endpoints - 管理員功能'
)]
#[OA\Tag(
    name: '參考資料',
    description: 'Reference Data endpoints - 產業、地區等參考資料'
)]
final class SwaggerController extends Controller
{
    /**
     * Display Swagger UI
     *
     * Returns the Swagger UI HTML page for interactive API documentation
     */
    public function index(): Response
    {
        // Only show in development/staging
        if (config('app.env') === 'production' && !config('app.debug')) {
            abort(404);
        }

        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - YAMU 業務推廣系統</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .swagger-ui .topbar {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/docs/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
                filter: true,
                showExtensions: true,
                showCommonExtensions: true,
                syntaxHighlight: {
                    activate: true,
                    theme: "agate"
                }
            });
        }
    </script>
</body>
</html>
HTML;

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Generate OpenAPI JSON specification
     *
     * Scans controllers and generates OpenAPI 3.0 JSON
     */
    public function json(): JsonResponse
    {
        // Only show in development/staging
        if (config('app.env') === 'production' && !config('app.debug')) {
            abort(404);
        }

        try {
            $openapi = \OpenApi\Generator::scan([
                app_path('Http/Controllers/Api'),
                app_path('Http/Requests'),
            ]);

            return response()->json(
                json_decode($openapi->toJson(), true),
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            )->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate OpenAPI specification',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
