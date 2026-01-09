<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name',
        'tax_id',
        'industry_id',
        'address',
        'phone',
        'approval_status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[200]',
        'tax_id' => 'required|exact_length[8]|is_unique[companies.tax_id,id,{id}]',
        'industry_id' => 'required|is_natural_no_zero',
        'approval_status' => 'in_list[pending,approved,rejected]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => '公司名稱為必填項目',
        ],
        'tax_id' => [
            'required' => '統一編號為必填項目',
            'exact_length' => '統一編號必須為 8 碼',
            'is_unique' => '此統一編號已被註冊',
        ],
        'industry_id' => [
            'required' => '產業類別為必填項目',
        ],
    ];

    /**
     * 取得已審核通過的公司
     */
    public function getApprovedCompanies(): array
    {
        return $this->where('approval_status', 'approved')->findAll();
    }

    /**
     * 取得待審核的公司
     */
    public function getPendingCompanies(): array
    {
        return $this->where('approval_status', 'pending')->findAll();
    }

    /**
     * 更新審核狀態
     */
    public function updateApprovalStatus(int $companyId, string $status, int $adminId): bool
    {
        $data = [
            'approval_status' => $status,
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($companyId, $data);
    }
}
