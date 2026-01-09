<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRejectedReasonFields extends Migration
{
    public function up()
    {
        // 修改 experiences 表的 approval_status，添加 'rejected' 選項
        $this->db->query("ALTER TABLE experiences MODIFY COLUMN approval_status ENUM('pending','approved','rejected') DEFAULT 'approved'");

        // 添加 rejected_reason 欄位到 experiences 表
        $this->forge->addColumn('experiences', [
            'rejected_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approval_status',
            ],
        ]);

        // 添加 rejected_reason 欄位到 certifications 表
        $this->forge->addColumn('certifications', [
            'rejected_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approval_status',
            ],
        ]);

        // 添加 description 欄位到 certifications 表（如果前端需要）
        if (!$this->db->fieldExists('description', 'certifications')) {
            $this->forge->addColumn('certifications', [
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'expiry_date',
                ],
            ]);
        }
    }

    public function down()
    {
        // 移除 rejected_reason 欄位
        $this->forge->dropColumn('experiences', 'rejected_reason');
        $this->forge->dropColumn('certifications', 'rejected_reason');

        // 移除 description 欄位（如果是新添加的）
        if ($this->db->fieldExists('description', 'certifications')) {
            $this->forge->dropColumn('certifications', 'description');
        }

        // 還原 experiences 表的 approval_status
        $this->db->query("ALTER TABLE experiences MODIFY COLUMN approval_status ENUM('pending','approved') DEFAULT 'approved'");
    }
}
