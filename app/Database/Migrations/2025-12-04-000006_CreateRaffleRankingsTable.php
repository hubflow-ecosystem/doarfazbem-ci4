<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaffleRankingsTable extends Migration
{
    public function up()
    {
        // Ranking de maiores compradores (Top 3 ganham prêmios)
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'buyer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'buyer_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'total_numbers' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'total_spent' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'position' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'prize_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'prize_paid' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'prize_paid_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey(['raffle_id', 'user_id'], false, true, 'uk_raffle_user');
        $this->forge->addKey(['raffle_id', 'total_numbers'], false, false, 'idx_ranking');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_rankings');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_rankings');
    }
}
