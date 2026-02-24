<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRafflesTable extends Migration
{
    public function up()
    {
        // Tabela principal de rifas
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'total_numbers' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1000000, // 1 milhão padrão
            ],
            'number_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 1.10,
            ],
            'main_prize_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 40.00, // 40% do arrecadado
            ],
            'campaign_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 40.00, // 40% para campanhas
            ],
            'platform_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 60.00, // 60% para plataforma (inclui prêmio)
            ],
            'federal_lottery_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'federal_lottery_result' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'winning_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'active', 'sold_out', 'drawing', 'finished', 'cancelled'],
                'default' => 'draft',
            ],
            'numbers_sold' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'total_revenue' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->createTable('raffles');
    }

    public function down()
    {
        $this->forge->dropTable('raffles');
    }
}
