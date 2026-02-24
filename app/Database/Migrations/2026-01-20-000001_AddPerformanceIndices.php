<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para adicionar indices de performance
 * Melhora consultas frequentes em colunas de filtro e ordenacao
 */
class AddPerformanceIndices extends Migration
{
    public function up()
    {
        // Indices para tabela donations
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_donations_status ON donations(status)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_donations_campaign_status ON donations(campaign_id, status)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_donations_user_id ON donations(user_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_donations_created_at ON donations(created_at)');

        // Indices para tabela campaigns
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_campaigns_status ON campaigns(status)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_campaigns_user_id ON campaigns(user_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_campaigns_category ON campaigns(category)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_campaigns_featured ON campaigns(is_featured)');

        // Indices para tabela raffle_purchases
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_raffle_purchases_status ON raffle_purchases(payment_status)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_raffle_purchases_raffle ON raffle_purchases(raffle_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_raffle_purchases_user ON raffle_purchases(user_id)');

        // Indices para tabela raffle_numbers
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_raffle_numbers_status ON raffle_numbers(status)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_raffle_numbers_raffle_status ON raffle_numbers(raffle_id, status)');

        // Indices para tabela users
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)');

        // Indices para tabela audit_logs (indice composto para consultas frequentes)
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_audit_logs_action_created ON audit_logs(action, created_at)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_audit_logs_user_action ON audit_logs(user_id, action)');

        // Indices para tabela asaas_transactions
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_asaas_transactions_payment_id ON asaas_transactions(asaas_payment_id)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_asaas_transactions_status ON asaas_transactions(status)');

        // Indice para webhook_processed (ja deve ter unique, mas garantir indice de busca)
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_webhook_processed_source ON webhook_processed(source)');
    }

    public function down()
    {
        // Remover indices criados (MySQL syntax)
        $indices = [
            'donations' => ['idx_donations_status', 'idx_donations_campaign_status', 'idx_donations_user_id', 'idx_donations_created_at'],
            'campaigns' => ['idx_campaigns_status', 'idx_campaigns_user_id', 'idx_campaigns_category', 'idx_campaigns_featured'],
            'raffle_purchases' => ['idx_raffle_purchases_status', 'idx_raffle_purchases_raffle', 'idx_raffle_purchases_user'],
            'raffle_numbers' => ['idx_raffle_numbers_status', 'idx_raffle_numbers_raffle_status'],
            'users' => ['idx_users_email', 'idx_users_role', 'idx_users_status'],
            'audit_logs' => ['idx_audit_logs_action_created', 'idx_audit_logs_user_action'],
            'asaas_transactions' => ['idx_asaas_transactions_payment_id', 'idx_asaas_transactions_status'],
            'webhook_processed' => ['idx_webhook_processed_source'],
        ];

        foreach ($indices as $table => $tableIndices) {
            foreach ($tableIndices as $index) {
                try {
                    $this->db->query("DROP INDEX {$index} ON {$table}");
                } catch (\Exception $e) {
                    // Ignorar se indice nao existir
                }
            }
        }
    }
}
