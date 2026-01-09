<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'username',
        'email',
        'password',        // 允許接收明文密碼（會被 beforeInsert hook 雜湊）
        'password_hash',
        'role',
        'status',
        'email_verified_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[8]',  // 允許明文密碼
        'password_hash' => 'permit_empty',            // 允許雜湊密碼（由 beforeInsert 產生）
        'role' => 'required|in_list[admin,salesperson,user]',
        'status' => 'in_list[pending,active,inactive]',
    ];

    protected $validationMessages = [
        'username' => [
            'required' => '使用者名稱為必填項目',
            'min_length' => '使用者名稱至少需要 3 個字元',
            'is_unique' => '此使用者名稱已被使用',
        ],
        'email' => [
            'required' => 'Email 為必填項目',
            'valid_email' => 'Email 格式不正確',
            'is_unique' => '此 Email 已被註冊',
        ],
        'role' => [
            'required' => '角色為必填項目',
            'in_list' => '角色必須為 admin, salesperson 或 user',
        ],
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * 密碼雜湊處理 (插入前)
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * 驗證密碼
     *
     * @param string $inputPassword 輸入的明文密碼
     * @param string $hashedPassword 資料庫中的雜湊密碼
     * @return bool
     */
    public function verifyPassword(string $inputPassword, string $hashedPassword): bool
    {
        return password_verify($inputPassword, $hashedPassword);
    }

    /**
     * 依 Email 查詢使用者
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * 依 Username 查詢使用者
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    /**
     * 取得所有業務員
     *
     * @param string $status 狀態篩選 (pending, active, inactive)
     * @return array
     */
    public function getSalespersons(string $status = null): array
    {
        $builder = $this->where('role', 'salesperson');

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    /**
     * 更新使用者狀態
     *
     * @param int $userId
     * @param string $status (pending, active, inactive)
     * @return bool
     */
    public function updateStatus(int $userId, string $status): bool
    {
        return $this->update($userId, ['status' => $status]);
    }
}
