<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Corrige incompatibilidade entre coluna payment_status (criada pela migration)
 * e o código que usa 'status' em todas as queries.
 *
 * Também adiciona 'received' ao ENUM pois o Asaas usa RECEIVED como status
 * de pagamento confirmado (distinto de CONFIRMED).
 */
class FixDonationsStatusColumn extends Migration
{
    public function up()
    {
        // 1. Renomear payment_status → status (código usa 'status' em todo lugar)
        $this->db->query("ALTER TABLE donations RENAME COLUMN payment_status TO status");

        // 2. Adicionar 'received' ao ENUM (Asaas envia RECEIVED além de CONFIRMED)
        $this->db->query("ALTER TABLE donations MODIFY COLUMN status
            ENUM('pending', 'confirmed', 'overdue', 'refunded', 'cancelled', 'received')
            NOT NULL DEFAULT 'pending'");

        // 3. Recriar índices com nomes corretos (MySQL pode preservar nome antigo)
        try {
            $this->db->query("DROP INDEX idx_donations_payment_status ON donations");
        } catch (\Exception $e) { /* já não existe */ }

        try {
            $this->db->query("DROP INDEX idx_donations_campaign_status ON donations");
        } catch (\Exception $e) { /* será recriado */ }

        try {
            $this->db->query("CREATE INDEX idx_donations_status ON donations(status)");
        } catch (\Exception $e) { /* já existe */ }

        try {
            $this->db->query("CREATE INDEX idx_donations_campaign_status ON donations(campaign_id, status)");
        } catch (\Exception $e) { /* já existe */ }
    }

    public function down()
    {
        // Desfaz renomeação
        try {
            $this->db->query("DROP INDEX idx_donations_status ON donations");
        } catch (\Exception $e) {}

        try {
            $this->db->query("DROP INDEX idx_donations_campaign_status ON donations");
        } catch (\Exception $e) {}

        $this->db->query("ALTER TABLE donations RENAME COLUMN status TO payment_status");

        $this->db->query("ALTER TABLE donations MODIFY COLUMN payment_status
            ENUM('pending', 'confirmed', 'overdue', 'refunded', 'cancelled')
            NOT NULL DEFAULT 'pending'");

        try {
            $this->db->query("CREATE INDEX idx_donations_payment_status ON donations(payment_status)");
        } catch (\Exception $e) {}

        try {
            $this->db->query("CREATE INDEX idx_donations_campaign_status ON donations(campaign_id, payment_status)");
        } catch (\Exception $e) {}
    }
}
