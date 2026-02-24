<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDonationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'NULL se doação anônima',
            ],
            'donor_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nome do doador (pode ser anônimo)',
            ],
            'donor_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'donor_cpf' => [
                'type' => 'VARCHAR',
                'constraint' => 14,
                'null' => true,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Valor da doação',
            ],
            'gateway_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Taxa do gateway (Asaas)',
            ],
            'platform_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Taxa da plataforma',
            ],
            'net_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Valor líquido para o criador',
            ],
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['pix', 'credit_card', 'boleto'],
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'overdue', 'refunded', 'cancelled'],
                'default' => 'pending',
            ],
            'asaas_payment_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'ID da cobrança no Asaas',
            ],
            'asaas_customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'ID do cliente no Asaas',
            ],
            'pix_qr_code' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'QR Code PIX (base64 ou URL)',
            ],
            'pix_copy_paste' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Código PIX Copia e Cola',
            ],
            'boleto_url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'URL do boleto bancário',
            ],
            'is_anonymous' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_recurring' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Se é doação recorrente',
            ],
            'subscription_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'ID da assinatura no Asaas (se recorrente)',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Mensagem do doador para o criador',
            ],
            'paid_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Data/hora do pagamento confirmado',
            ],
            'refunded_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_response' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON da resposta completa da API Asaas',
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
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['payment_status', 'created_at']);
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('donations');
    }

    public function down()
    {
        $this->forge->dropTable('donations');
    }
}
