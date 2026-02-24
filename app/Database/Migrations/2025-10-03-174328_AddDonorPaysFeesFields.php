<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adiciona campos para suportar "Doador Paga as Taxas"
 */
class AddDonorPaysFeesFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('donations', [
            'charged_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'amount',
                'comment'    => 'Valor cobrado do doador (pode ser maior se ele paga taxas)'
            ],
            'donor_pays_fees' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'after'      => 'net_amount',
                'comment'    => 'Se TRUE, doador pagou as taxas e criador recebe 100%'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('donations', ['charged_amount', 'donor_pays_fees']);
    }
}
