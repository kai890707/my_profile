<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificationModel extends Model
{
    protected $table = 'certifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'issuer',
        'issue_date',
        'expiry_date',
        'file_data',
        'file_mime',
        'file_size',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'name' => 'required|min_length[2]|max_length[200]',
        'issuer' => 'required|min_length[2]|max_length[200]',
        'issue_date' => 'required|valid_date',
        'approval_status' => 'in_list[pending,approved,rejected]',
    ];

    /**
     * 依使用者 ID 取得證照列表
     */
    public function getByUserId(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * 取得待審核的證照
     */
    public function getPendingCertifications(): array
    {
        // 不包含 BLOB 欄位，避免 JSON 編碼錯誤
        return $this->select('id, user_id, name, issuer, issue_date, expiry_date, description, approval_status, approved_by, approved_at, created_at, updated_at')
            ->where('approval_status', 'pending')
            ->findAll();
    }

    /**
     * 更新審核狀態
     */
    public function updateApprovalStatus(int $certId, string $status, int $adminId): bool
    {
        $data = [
            'approval_status' => $status,
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($certId, $data);
    }
}
