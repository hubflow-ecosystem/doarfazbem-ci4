<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRafflePurchasesTable extends Migration
{
    public function up()
    {
        // Compras de cotas
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
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
            'buyer_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'buyer_cpf' => [
                'type' => 'VARCHAR',
                'constraint' => 14,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'discount_applied' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pix',
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'expired', 'refunded', 'cancelled'],
                'default' => 'pending',
            ],
            'payment_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'pix_code' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pix_qrcode' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'instant_prize_won' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
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
        $this->forge->addKey('raffle_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('payment_status');
        $this->forge->addKey('payment_id');
        $this->forge->addForeignKey('raffle_id', 'raffles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raffle_purchases');
    }

    public function down()
    {
        $this->forge->dropTable('raffle_purchases');
    }
}
