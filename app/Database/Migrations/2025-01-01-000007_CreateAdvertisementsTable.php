<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdvertisementsTable extends Migration
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
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'link' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'position' => [
                'type'       => 'ENUM',
                'constraint' => ['banner_top', 'banner_side', 'banner_bottom', 'between_campaigns'],
                'default'    => 'banner_side',
            ],
            'target_category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true, // null = todas as categorias
            ],
            'impressions' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'clicks' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
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
        $this->forge->addKey('position');
        $this->forge->addKey('is_active');
        $this->forge->createTable('advertisements');
    }

    public function down()
    {
        $this->forge->dropTable('advertisements');
    }
}
