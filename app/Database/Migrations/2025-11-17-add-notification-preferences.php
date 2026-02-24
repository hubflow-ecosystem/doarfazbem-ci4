<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration para criar tabela de preferências de notificações
 */
class AddNotificationPreferences extends Migration
{
    public function up()
    {
        // Tabela de preferências de notificação
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID do usuário (NULL para doadores anônimos)',
                'null' => true,
            ],
            'donor_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Email do doador (para doadores não cadastrados)',
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID da campanha',
            ],
            'notify_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1 = recebe emails, 0 = não recebe',
            ],
            'notify_push' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1 = recebe push, 0 = não recebe',
            ],
            'push_token' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Token do Firebase Cloud Messaging',
            ],
            'unsubscribe_token' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'comment' => 'Token único para unsubscribe',
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('donor_email');
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('unsubscribe_token');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('notification_preferences');

        // Tabela de fila de notificações (para processar em background)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'campaign_update_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID da atualização da campanha',
            ],
            'recipient_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'recipient_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => ['email', 'push'],
                'default' => 'email',
            ],
            'push_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sent_at' => [
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
        $this->forge->addKey('campaign_update_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('campaign_update_id', 'campaign_updates', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('notification_queue');
    }

    public function down()
    {
        $this->forge->dropTable('notification_queue', true);
        $this->forge->dropTable('notification_preferences', true);
    }
}
