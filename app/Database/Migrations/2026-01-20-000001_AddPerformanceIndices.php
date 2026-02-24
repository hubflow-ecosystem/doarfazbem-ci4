<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para adicionar indices de performance
 * Melhora consultas frequentes em colunas de filtro e ordenacao
 */
class AddPerformanceIndices extends Migration
{
    /**
     * Cria índice de forma segura (sem IF NOT EXISTS - não suportado no MySQL para índices)
     */
    private function createIndex(string $name, string $table, string $columns): void
    {
        try {
            $this->db->query("CREATE INDEX {$name} ON {$table}({$columns})");
        } catch (\Exception $e) {
            // Índice já existe - ignorar
        }
    }

    public function up()
    {
        // Índices para tabela donations (payment_status, não status)
        $this->createIndex('idx_donations_payment_status', 'donations', 'payment_status');
        $this->createIndex('idx_donations_campaign_status', 'donations', 'campaign_id, payment_status');
        $this->createIndex('idx_donations_user_id', 'donations', 'user_id');
        $this->createIndex('idx_donations_created_at', 'donations', 'created_at');

        // Índices para tabela campaigns
        $this->createIndex('idx_campaigns_status', 'campaigns', 'status');
        $this->createIndex('idx_campaigns_user_id', 'campaigns', 'user_id');
        $this->createIndex('idx_campaigns_category', 'campaigns', 'category');
        $this->createIndex('idx_campaigns_featured', 'campaigns', 'is_featured');

        // Índices para tabela raffle_purchases
        $this->createIndex('idx_raffle_purchases_status', 'raffle_purchases', 'payment_status');
        $this->createIndex('idx_raffle_purchases_raffle', 'raffle_purchases', 'raffle_id');
        $this->createIndex('idx_raffle_purchases_user', 'raffle_purchases', 'user_id');

        // Índices para tabela raffle_numbers
        $this->createIndex('idx_raffle_numbers_status', 'raffle_numbers', 'status');
        $this->createIndex('idx_raffle_numbers_raffle_status', 'raffle_numbers', 'raffle_id, status');

        // Índices para tabela users
        $this->createIndex('idx_users_email', 'users', 'email');
        $this->createIndex('idx_users_role', 'users', 'role');
        $this->createIndex('idx_users_status', 'users', 'status');

        // Índices para tabela audit_logs
        $this->createIndex('idx_audit_logs_action_created', 'audit_logs', 'action, created_at');
        $this->createIndex('idx_audit_logs_user_action', 'audit_logs', 'user_id, action');

        // Índices para tabela asaas_transactions
        $this->createIndex('idx_asaas_transactions_payment_id', 'asaas_transactions', 'asaas_payment_id');
        $this->createIndex('idx_asaas_transactions_status', 'asaas_transactions', 'status');

        // Índice para webhook_processed
        $this->createIndex('idx_webhook_processed_source', 'webhook_processed', 'source');
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
