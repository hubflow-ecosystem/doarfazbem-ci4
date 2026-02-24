<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHighlightsFieldToCampaigns extends Migration
{
    public function up()
    {
        // Adicionar campo para "Por que apoiar?" (highlights JSON)
        $this->forge->addColumn('campaigns', [
            'highlights' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description',
                'comment' => 'JSON com destaques: Por que apoiar?',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', 'highlights');
    }
}
