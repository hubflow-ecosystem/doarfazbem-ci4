<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela para controle de idempotencia de webhooks
 * Previne processamento duplicado de webhooks
 */
class CreateWebhookProcessedTable extends Migration
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
            'webhook_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => 'asaas',
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['source', 'webhook_id'], 'uk_source_webhook_id');
        $this->forge->addKey('processed_at', false, false, 'idx_processed_at');

        $this->forge->createTable('webhook_processed', true);
    }

    public function down()
    {
        $this->forge->dropTable('webhook_processed', true);
    }
}
