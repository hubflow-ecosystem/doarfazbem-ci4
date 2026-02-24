<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRafflePackagesTable extends Migration
{
    public function up()
    {
        // Pacotes de cotas com descontos progressivos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'raffle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'original_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'discount_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'discount_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
            ],
            'is_popular' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('raffle_id');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_packages');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_packages');
    }
}
