<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela de Assinaturas (Doações Recorrentes)
 */
class AddSubscriptionsTable extends Migration
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
            'campaign_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID da campanha recorrente',
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID do usuário doador (NULL se não cadastrado)',
            ],
            'donor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Nome do doador',
            ],
            'donor_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Email do doador',
            ],
            'donor_cpf' => [
                'type'       => 'VARCHAR',
                'constraint' => 14,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'comment'    => 'Valor mensal da assinatura',
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['credit_card', 'boleto'],
                'comment'    => 'Método de pagamento (recorrente só aceita cartão ou boleto)',
            ],
            'cycle' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'quarterly', 'semiannual', 'yearly'],
                'default'    => 'monthly',
                'comment'    => 'Ciclo de cobrança',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'cancelled', 'suspended', 'expired'],
                'default'    => 'active',
                'comment'    => 'Status da assinatura',
            ],
            'asaas_subscription_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'ID da assinatura no Asaas',
            ],
            'asaas_customer_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'ID do cliente no Asaas',
            ],
            'next_due_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Próxima data de cobrança',
            ],
            'started_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Data de início da assinatura',
            ],
            'cancelled_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Data de cancelamento',
            ],
            'api_response' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON da resposta da API Asaas',
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
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('asaas_subscription_id');
        $this->forge->addKey(['status', 'next_due_date']);

        $this->forge->createTable('subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions');
    }
}
