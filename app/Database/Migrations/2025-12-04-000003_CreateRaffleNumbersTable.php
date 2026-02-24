<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaffleNumbersTable extends Migration
{
    public function up()
    {
        // Números individuais da rifa
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'raffle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'number' => [
                'type' => 'VARCHAR',
                'constraint' => 10, // Ex: 000001 a 999999
            ],
            'purchase_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['available', 'reserved', 'paid', 'winner'],
                'default' => 'available',
            ],
            'is_special_prize' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'special_prize_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reservation_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['raffle_id', 'number'], false, true, 'uk_raffle_number');
        $this->forge->addKey('purchase_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['status', 'reservation_expires_at'], false, false, 'idx_reservation_cleanup');
        $this->forge->addKey('is_special_prize');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_numbers');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_numbers');
    }
}
