<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateNotificationTables extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:create-notification-tables';
    protected $description = 'Cria tabelas de preferências de notificação';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('Criando tabelas de preferências de notificação...', 'yellow');
        CLI::newLine();

        // SQL commands
        $commands = [
            // Table campaign_creator_preferences
            "CREATE TABLE IF NOT EXISTS `campaign_creator_preferences` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do criador da campanha (users.id)',
              `campaign_id` INT(11) UNSIGNED NULL COMMENT 'ID da campanha (null = preferências globais do usuário)',
              `notify_donation_email` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Receber email ao receber doação',
              `notify_donation_push` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Receber push ao receber doação',
              `notify_daily_summary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Receber resumo diário',
              `notify_weekly_summary` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Receber resumo semanal',
              `push_token` VARCHAR(255) NULL COMMENT 'Token FCM para push notifications',
              `created_at` DATETIME NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              KEY `campaign_id` (`campaign_id`),
              UNIQUE KEY `unique_user_campaign` (`user_id`, `campaign_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            // Table admin_notification_preferences
            "CREATE TABLE IF NOT EXISTS `admin_notification_preferences` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `admin_user_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID do usuário admin (users.id)',
              `notify_new_campaign_email` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Email quando nova campanha é criada',
              `notify_new_campaign_push` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Push quando nova campanha é criada',
              `notify_weekly_donations_report` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Relatório semanal de doações (10% em 10% por campanha)',
              `notify_campaign_milestones` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Notificar quando campanhas atingem marcos de 10% em 10%',
              `enable_realtime_dashboard` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Habilitar notificações em tempo real no dashboard',
              `push_token` VARCHAR(255) NULL COMMENT 'Token FCM para push notifications',
              `created_at` DATETIME NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              KEY `admin_user_id` (`admin_user_id`),
              UNIQUE KEY `unique_admin_user` (`admin_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            // Table campaign_milestones_notified
            "CREATE TABLE IF NOT EXISTS `campaign_milestones_notified` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `campaign_id` INT(11) UNSIGNED NOT NULL,
              `milestone_percentage` INT(3) NOT NULL COMMENT 'Porcentagem atingida (10, 20, 30, ..., 100)',
              `notified_at` DATETIME NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_campaign_milestone` (`campaign_id`, `milestone_percentage`),
              KEY `campaign_id` (`campaign_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rastreia marcos de campanha já notificados'"
        ];

        foreach ($commands as $sql) {
            try {
                $db->query($sql);
                CLI::write('✓ Tabela criada com sucesso', 'green');
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    CLI::write('⚠ Tabela já existe', 'yellow');
                } else {
                    CLI::write('✗ Erro: ' . $e->getMessage(), 'red');
                }
            }
        }

        CLI::newLine();
        CLI::write('Adicionando colunas em notification_preferences...', 'yellow');

        // Add columns to notification_preferences
        $alterCommands = [
            "ALTER TABLE `notification_preferences` ADD COLUMN `notify_campaign_goal_reached` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Notificar quando campanha atingir meta' AFTER `notify_push`",
            "ALTER TABLE `notification_preferences` ADD COLUMN `notify_campaign_ending_soon` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Notificar quando campanha estiver perto de acabar (7 dias)' AFTER `notify_campaign_goal_reached`"
        ];

        foreach ($alterCommands as $sql) {
            try {
                $db->query($sql);
                CLI::write('✓ Coluna adicionada com sucesso', 'green');
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    CLI::write('⚠ Coluna já existe', 'yellow');
                } else {
                    CLI::write('✗ Erro: ' . $e->getMessage(), 'red');
                }
            }
        }

        CLI::newLine();
        CLI::write('✓ Processo concluído!', 'green');
    }
}
