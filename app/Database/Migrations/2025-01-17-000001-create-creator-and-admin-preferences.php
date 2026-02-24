<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Criar tabelas de preferências de notificação
 * - campaign_creator_preferences: preferências dos criadores de campanhas
 * - admin_notification_preferences: preferências do admin da plataforma
 */
class CreateCreatorAndAdminPreferences extends Migration
{
    public function up()
    {
        // ========================================================================
        // TABELA: campaign_creator_preferences
        // Gerencia preferências de notificação dos CRIADORES de campanhas
        // ========================================================================
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
                'comment' => 'ID do criador da campanha (users.id)',
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID da campanha (null = preferências globais do usuário)',
            ],

            // Preferências de notificação por doação
            'notify_donation_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Receber email ao receber doação',
            ],
            'notify_donation_push' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Receber push ao receber doação',
            ],

            // Preferências de resumo periódico
            'notify_daily_summary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Receber resumo diário',
            ],
            'notify_weekly_summary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Receber resumo semanal',
            ],

            // Token push (opcional, pode usar do notification_preferences também)
            'push_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Token FCM para push notifications',
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
        $this->forge->addKey('campaign_id');
        $this->forge->addUniqueKey(['user_id', 'campaign_id'], 'unique_user_campaign');
        $this->forge->createTable('campaign_creator_preferences');

        // ========================================================================
        // TABELA: admin_notification_preferences
        // Gerencia preferências de notificação do ADMIN da plataforma
        // ========================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'admin_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID do usuário admin (users.id)',
            ],

            // Notificações de novas campanhas
            'notify_new_campaign_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Email quando nova campanha é criada',
            ],
            'notify_new_campaign_push' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Push quando nova campanha é criada',
            ],

            // Relatório semanal de doações
            'notify_weekly_donations_report' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Relatório semanal de doações (10% em 10% por campanha)',
            ],

            // Notificações de marcos de campanha (10%, 20%, ..., 100%)
            'notify_campaign_milestones' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Notificar quando campanhas atingem marcos de 10% em 10%',
            ],

            // Dashboard em tempo real
            'enable_realtime_dashboard' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Habilitar notificações em tempo real no dashboard',
            ],

            // Token push
            'push_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Token FCM para push notifications',
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
        $this->forge->addKey('admin_user_id');
        $this->forge->addUniqueKey('admin_user_id', 'unique_admin_user');
        $this->forge->createTable('admin_notification_preferences');

        // ========================================================================
        // ADICIONAR COLUNAS em notification_preferences para doadores
        // ========================================================================
        $fields = [
            'notify_campaign_goal_reached' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Notificar quando campanha atingir meta',
                'after' => 'notify_push',
            ],
            'notify_campaign_ending_soon' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Notificar quando campanha estiver perto de acabar (7 dias)',
                'after' => 'notify_campaign_goal_reached',
            ],
        ];

        $this->forge->addColumn('notification_preferences', $fields);
    }

    public function down()
    {
        // Remover colunas adicionadas em notification_preferences
        $this->forge->dropColumn('notification_preferences', ['notify_campaign_goal_reached', 'notify_campaign_ending_soon']);

        // Remover tabelas criadas
        $this->forge->dropTable('admin_notification_preferences', true);
        $this->forge->dropTable('campaign_creator_preferences', true);
    }
}
