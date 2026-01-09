<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExperiences extends Migration
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
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'start_date' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'end_date' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('experiences');
    }

    public function down()
    {
        $this->forge->dropTable('experiences');
    }
}
