<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends ResourceController
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * Response format (預設為 JSON)
     */
    protected $format = 'json';

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * 統一成功回應格式
     *
     * @param mixed $data 回應資料
     * @param string $message 成功訊息
     * @param int $statusCode HTTP 狀態碼
     * @return ResponseInterface
     */
    protected function respondSuccess($data = null, string $message = '操作成功', int $statusCode = 200): ResponseInterface
    {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->response
            ->setJSON($response)
            ->setStatusCode($statusCode);
    }

    /**
     * 統一錯誤回應格式
     *
     * @param string $message 錯誤訊息
     * @param int $statusCode HTTP 狀態碼
     * @param mixed $errors 詳細錯誤資訊
     * @return ResponseInterface
     */
    protected function respondError(string $message = '操作失敗', int $statusCode = 400, $errors = null): ResponseInterface
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response
            ->setJSON($response)
            ->setStatusCode($statusCode);
    }

    /**
     * 401 Unauthorized 回應
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function respondUnauthorized(string $message = '未授權，請先登入'): ResponseInterface
    {
        return $this->respondError($message, 401);
    }

    /**
     * 403 Forbidden 回應
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function respondForbidden(string $message = '權限不足'): ResponseInterface
    {
        return $this->respondError($message, 403);
    }

    /**
     * 404 Not Found 回應
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function respondNotFound(string $message = '資源不存在'): ResponseInterface
    {
        return $this->respondError($message, 404);
    }

    /**
     * 422 Validation Error 回應
     *
     * @param array $errors 驗證錯誤
     * @return ResponseInterface
     */
    protected function respondValidationError(array $errors): ResponseInterface
    {
        return $this->respondError('資料驗證失敗', 422, $errors);
    }

    /**
     * 從 Request 中取得當前使用者資訊
     *
     * @return array|null
     */
    protected function getCurrentUser(): ?array
    {
        return $this->request->user ?? null;
    }

    /**
     * 從 Request 中取得當前使用者 ID
     *
     * @return int|null
     */
    protected function getCurrentUserId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user['sub'] ?? null;
    }

    /**
     * 從 Request 中取得當前使用者角色
     *
     * @return string|null
     */
    protected function getCurrentUserRole(): ?string
    {
        $user = $this->getCurrentUser();
        return $user['role'] ?? null;
    }
}
