<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    /**
     * 執行角色驗證
     *
     * @param RequestInterface $request
     * @param array|null $arguments 允許的角色列表 (例如: ['admin', 'salesperson'])
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 檢查是否已通過 AuthFilter (user 資訊是否存在)
        if (!isset($request->user) || !isset($request->user['role'])) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => '請先登入',
                ])
                ->setStatusCode(401);
        }

        $userRole = $request->user['role'];

        // 如果沒有指定允許的角色，則不進行檢查
        if (empty($arguments)) {
            return $request;
        }

        // 檢查使用者角色是否在允許的角色列表中
        if (!in_array($userRole, $arguments, true)) {
            return Services::response()
                ->setJSON([
                    'status' => 'error',
                    'message' => '權限不足',
                    'required_role' => $arguments,
                    'your_role' => $userRole,
                ])
                ->setStatusCode(403);
        }

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
