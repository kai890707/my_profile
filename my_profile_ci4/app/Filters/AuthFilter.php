<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWTLib;
use Config\Services;

class AuthFilter implements FilterInterface
{
    /**
     * 執行驗證前處理
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtLib = new JWTLib();

        // 取得 Authorization Header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => '缺少 Authorization Header',
                ])
                ->setStatusCode(401);
        }

        // 提取 Bearer Token
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authorization Header 格式不正確',
                ])
                ->setStatusCode(401);
        }

        $token = $matches[1];

        // 驗證 Token
        $payload = $jwtLib->verifyToken($token);

        if (!$payload) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token 無效或已過期',
                ])
                ->setStatusCode(401);
        }

        // 檢查是否為 Access Token
        if (!$jwtLib->validateTokenType($payload, 'access')) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token 類型不正確',
                ])
                ->setStatusCode(401);
        }

        // 將使用者資訊注入 Request
        $request->user = (array) $payload;

        return $request;
    }

    /**
     * 執行回應後處理
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
