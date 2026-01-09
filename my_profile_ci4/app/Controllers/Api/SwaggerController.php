<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Generator;

/**
 * Swagger API Documentation Controller
 *
 * 提供 Swagger UI 介面和 OpenAPI JSON 規格
 * 僅在開發環境可用
 */
class SwaggerController extends ResourceController
{
    protected $format = 'json';

    /**
     * @var string Controller 掃描路徑
     */
    protected $controllersPath;

    /**
     * @var bool 是否啟用 Swagger
     */
    protected $enabled;

    /**
     * Constructor
     *
     * 初始化 Controller，設定掃描路徑和啟用狀態
     */
    public function __construct()
    {
        $this->controllersPath = APPPATH . 'Controllers/Api';
        $this->enabled = env('SWAGGER_ENABLED', true) &&
                        env('CI_ENVIRONMENT') === 'development';
    }

    /**
     * GET /api/docs
     *
     * 顯示 Swagger UI 介面
     *
     * @return \CodeIgniter\HTTP\Response HTML 頁面或 404
     */
    public function index()
    {
        // 檢查環境
        if (!$this->enabled) {
            return $this->failNotFound('Not Found');
        }

        // 返回 Swagger UI HTML
        return $this->response
            ->setContentType('text/html')
            ->setBody($this->getSwaggerUI());
    }

    /**
     * GET /api/docs/openapi.json
     *
     * 返回 OpenAPI 3.0 JSON 規格
     *
     * @return \CodeIgniter\HTTP\Response JSON 規格或 404
     */
    public function json()
    {
        // 檢查環境
        if (!$this->enabled) {
            return $this->failNotFound('Not Found');
        }

        try {
            // 掃描 Controllers 目錄生成 OpenAPI JSON
            $openapi = Generator::scan([$this->controllersPath]);

            return $this->response
                ->setContentType('application/json')
                ->setHeader('Cache-Control', 'no-cache')
                ->setBody($openapi->toJson());

        } catch (\Exception $e) {
            log_message('error', 'Swagger generation failed: ' . $e->getMessage());

            return $this->failServerError(
                'Failed to generate OpenAPI specification',
                500
            );
        }
    }

    /**
     * 生成 Swagger UI HTML
     *
     * @return string HTML 內容
     */
    protected function getSwaggerUI(): string
    {
        $apiTitle = env('SWAGGER_API_TITLE', 'API Documentation');
        $deepLinking = env('SWAGGER_UI_DEEP_LINKING', true) ? 'true' : 'false';
        $displayRequestDuration = env('SWAGGER_UI_DISPLAY_REQUEST_DURATION', true) ? 'true' : 'false';

        return <<<HTML
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$apiTitle}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
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
                deepLinking: {$deepLinking},
                displayRequestDuration: {$displayRequestDuration},
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true
            });
        };
    </script>
</body>
</html>
HTML;
    }
}
