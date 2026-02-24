<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adiciona campos de localização geográfica para Google Maps
 */
class AddLocationFieldsToCampaigns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaigns', [
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'video_url',
                'comment' => 'Cidade da campanha'
            ],
            'state' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
                'null' => true,
                'after' => 'city',
                'comment' => 'UF do estado (ex: SP, RJ, MG)'
            ],
            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => 'Brasil',
                'after' => 'state',
                'comment' => 'País da campanha'
            ],
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
                'after' => 'country',
                'comment' => 'Latitude para Google Maps'
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
                'after' => 'latitude',
                'comment' => 'Longitude para Google Maps'
            ],
        ]);

        // Adicionar índice para buscas por localização
        $this->forge->addKey(['city', 'state']);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', [
            'city',
            'state',
            'country',
            'latitude',
            'longitude'
        ]);
    }
}
