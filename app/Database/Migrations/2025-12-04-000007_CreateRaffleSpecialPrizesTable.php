<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaffleSpecialPrizesTable extends Migration
{
    public function up()
    {
        // Configuração de cotas premiadas especiais (111111, 222222, etc.)
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
            'number_pattern' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Ex: 111111, 222222, 123456',
            ],
            'prize_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Ex: Sequência Única, Número da Sorte',
            ],
            'prize_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'buyer_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 50.00, // 50% para o comprador
            ],
            'campaign_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 40.00, // 40% para campanhas
            ],
            'platform_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 10.00, // 10% para plataforma
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('raffle_id');
        $this->forge->addKey(['raffle_id', 'number_pattern'], false, true, 'uk_raffle_pattern');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_special_prizes');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_special_prizes');
    }
}
