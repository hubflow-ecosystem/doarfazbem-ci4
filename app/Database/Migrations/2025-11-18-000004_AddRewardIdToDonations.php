<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRewardIdToDonations extends Migration
{
    public function up()
    {
        // Adicionar campo para vincular doação à recompensa escolhida
        $this->forge->addColumn('donations', [
            'reward_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'campaign_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('donations', 'reward_id');
    }
}
