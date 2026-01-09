<?php

namespace App\Models;

use CodeIgniter\Model;

class ApprovalLogModel extends Model
{
    protected $table = 'approval_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'approvable_type',
        'approvable_id',
        'action',
        'admin_id',
        'reason',
    ];

    protected $useTimestamps = false; // 只有 created_at
    protected $createdField = 'created_at';

    protected $validationRules = [
        'approvable_type' => 'required|in_list[user,company,certification,experience]',
        'approvable_id' => 'required|is_natural_no_zero',
        'action' => 'required|in_list[approved,rejected]',
        'admin_id' => 'required|is_natural_no_zero',
    ];

    /**
     * 記錄審核操作
     *
     * @param string $type (user, company, certification, experience)
     * @param int $id
     * @param string $action (approved, rejected)
     * @param int $adminId
     * @param string|null $reason
     * @return bool
     */
    public function logApproval(string $type, int $id, string $action, int $adminId, ?string $reason = null): bool
    {
        $data = [
            'approvable_type' => $type,
            'approvable_id' => $id,
            'action' => $action,
            'admin_id' => $adminId,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data) !== false;
    }

    /**
     * 取得指定項目的審核記錄
     */
    public function getApprovalHistory(string $type, int $id): array
    {
        return $this->where('approvable_type', $type)
            ->where('approvable_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
