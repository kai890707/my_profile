<?php

namespace App\Models;

use CodeIgniter\Model;

class SalespersonProfileModel extends Model
{
    protected $table = 'salesperson_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'user_id',
        'company_id',
        'full_name',
        'phone',
        'bio',
        'specialties',
        'service_regions',
        'avatar_data',
        'avatar_mime',
        'avatar_size',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 自動轉換 JSON 欄位
    protected array $casts = [
        'service_regions' => '?json',  // ? 表示可以為 null
    ];

    protected $validationRules = [
        'user_id' => 'required|is_natural_no_zero',
        'full_name' => 'required|min_length[2]|max_length[100]',
        'phone' => 'permit_empty|regex_match[/^09\d{8}$/]',
        'approval_status' => 'in_list[pending,approved,rejected]',
    ];

    protected $validationMessages = [
        'full_name' => [
            'required' => '姓名為必填項目',
            'min_length' => '姓名至少需要 2 個字元',
        ],
        'phone' => [
            'regex_match' => '手機號碼格式不正確 (應為 09xxxxxxxx)',
        ],
    ];

    /**
     * 取得已審核通過的業務員資料
     */
    public function getApprovedProfiles(): array
    {
        return $this->where('approval_status', 'approved')->findAll();
    }

    /**
     * 取得待審核的業務員資料
     */
    public function getPendingProfiles(): array
    {
        // 不包含 BLOB 欄位，避免 JSON 編碼錯誤
        return $this->select('id, user_id, company_id, full_name, phone, bio, specialties, service_regions, approval_status, approved_by, approved_at, created_at, updated_at')
            ->where('approval_status', 'pending')
            ->findAll();
    }

    /**
     * 更新審核狀態
     *
     * @param int $profileId
     * @param string $status (approved, rejected)
     * @param int $adminId
     * @return bool
     */
    public function updateApprovalStatus(int $profileId, string $status, int $adminId): bool
    {
        $data = [
            'approval_status' => $status,
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($profileId, $data);
    }

    /**
     * 依使用者 ID 取得資料
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}
