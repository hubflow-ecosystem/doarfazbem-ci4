<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adiciona campo campaign_type para diferenciar entre tipos de campanha
 */
class AddCampaignTypeField extends Migration
{
    public function up()
    {
        $this->forge->addColumn('campaigns', [
            'campaign_type' => [
                'type'       => 'ENUM',
                'constraint' => ['flexivel', 'tudo_ou_tudo', 'recorrente'],
                'default'    => 'flexivel',
                'after'      => 'category',
                'comment'    => 'Tipo: flexivel (qualquer valor), tudo_ou_tudo (redistribui se não atingir), recorrente (mensal)'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', 'campaign_type');
    }
}
