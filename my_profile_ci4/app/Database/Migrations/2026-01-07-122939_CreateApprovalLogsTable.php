<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApprovalLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'approvable_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'approvable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'action' => [
                'type'       => 'ENUM',
                'constraint' => ['approved', 'rejected'],
            ],
            'admin_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['approvable_type', 'approvable_id']);
        $this->forge->addKey('admin_id');

        // Foreign key
        $this->forge->addForeignKey('admin_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('approval_logs');
    }

    public function down()
    {
        $this->forge->dropTable('approval_logs');
    }
}
