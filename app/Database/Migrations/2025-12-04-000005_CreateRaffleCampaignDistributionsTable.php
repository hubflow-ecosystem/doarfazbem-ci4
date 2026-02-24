<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaffleCampaignDistributionsTable extends Migration
{
    public function up()
    {
        // Distribuição de valores para campanhas escolhidas pelo comprador
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'purchase_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 20.00, // 20% de cada campanha (máx 5 = 100%)
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'transferred' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'transferred_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_id');
        $this->forge->addKey('campaign_id');
        $this->forge->addForeignKey('purchase_id', 'raffle_purchases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_campaign_distributions');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_campaign_distributions');
    }
}
