<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAsaasTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'donation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'subscription_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'asaas_payment_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'asaas_customer_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['pix', 'boleto', 'credit_card'],
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'received', 'overdue', 'refunded', 'cancelled'],
                'default'    => 'pending',
            ],
            'webhook_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'processed_at' => [
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
        $this->forge->addKey('asaas_payment_id');
        $this->forge->addKey('donation_id');
        $this->forge->addKey('status');

        $this->forge->addForeignKey('donation_id', 'donations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('subscription_id', 'subscriptions', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('asaas_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('asaas_transactions');
    }
}
