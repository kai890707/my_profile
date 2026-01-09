<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyExperiencesTable extends Migration
{
    public function up()
    {
        // Add new columns to experiences table
        $fields = [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved'],
                'default'    => 'approved',
                'after'      => 'description',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'approval_status',
            ],
            'approved_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'approved_by',
            ],
        ];

        $this->forge->addColumn('experiences', $fields);

        // Modify date columns from VARCHAR to DATE
        $this->forge->modifyColumn('experiences', [
            'start_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);

        // Add foreign key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'experiences_user_id_fk');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE', 'experiences_approved_by_fk');
        $this->forge->processIndexes('experiences');
    }

    public function down()
    {
        // Remove foreign keys first
        $this->db->query('ALTER TABLE experiences DROP FOREIGN KEY experiences_user_id_fk');
        $this->db->query('ALTER TABLE experiences DROP FOREIGN KEY experiences_approved_by_fk');

        // Drop added columns
        $this->forge->dropColumn('experiences', ['user_id', 'approval_status', 'approved_by', 'approved_at']);

        // Revert date columns to VARCHAR
        $this->forge->modifyColumn('experiences', [
            'start_date' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'end_date' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
        ]);
    }
}
