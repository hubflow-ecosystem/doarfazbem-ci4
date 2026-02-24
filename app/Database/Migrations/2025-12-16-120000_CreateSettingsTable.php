<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
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
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'setting_group' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'general',
            ],
            'setting_type' => [
                'type' => 'ENUM',
                'constraint' => ['string', 'int', 'float', 'bool', 'json', 'array'],
                'default' => 'string',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->addKey('setting_group');

        $this->forge->createTable('settings', true);

        // Inserir configurações padrão
        $this->insertDefaults();
    }

    public function down()
    {
        $this->forge->dropTable('settings', true);
    }

    private function insertDefaults()
    {
        $db = \Config\Database::connect();

        $settings = [
            // === GERAL ===
            ['setting_key' => 'platform_name', 'setting_value' => 'DoarFazBem', 'setting_group' => 'general', 'setting_type' => 'string', 'description' => 'Nome da plataforma'],
            ['setting_key' => 'contact_email', 'setting_value' => 'contato@doarfazbem.com.br', 'setting_group' => 'general', 'setting_type' => 'string', 'description' => 'Email de contato'],
            ['setting_key' => 'contact_phone', 'setting_value' => '', 'setting_group' => 'general', 'setting_type' => 'string', 'description' => 'Telefone de contato'],
            ['setting_key' => 'maintenance_mode', 'setting_value' => '0', 'setting_group' => 'general', 'setting_type' => 'bool', 'description' => 'Modo manutenção'],

            // === PAGAMENTOS - GATEWAYS ===
            ['setting_key' => 'asaas_environment', 'setting_value' => 'sandbox', 'setting_group' => 'payments', 'setting_type' => 'string', 'description' => 'Ambiente Asaas (sandbox/production)'],
            ['setting_key' => 'mercadopago_environment', 'setting_value' => 'sandbox', 'setting_group' => 'payments', 'setting_type' => 'string', 'description' => 'Ambiente MercadoPago (sandbox/production)'],
            ['setting_key' => 'payment_gateway_primary', 'setting_value' => 'asaas', 'setting_group' => 'payments', 'setting_type' => 'string', 'description' => 'Gateway primário'],

            // === PAGAMENTOS - MÉTODOS ===
            ['setting_key' => 'enable_pix', 'setting_value' => '1', 'setting_group' => 'payments', 'setting_type' => 'bool', 'description' => 'Habilitar PIX'],
            ['setting_key' => 'enable_credit_card', 'setting_value' => '1', 'setting_group' => 'payments', 'setting_type' => 'bool', 'description' => 'Habilitar Cartão'],
            ['setting_key' => 'enable_boleto', 'setting_value' => '1', 'setting_group' => 'payments', 'setting_type' => 'bool', 'description' => 'Habilitar Boleto'],

            // === PAGAMENTOS - TAXAS PLATAFORMA ===
            ['setting_key' => 'platform_fee_medical', 'setting_value' => '0', 'setting_group' => 'payments', 'setting_type' => 'float', 'description' => 'Taxa para campanhas médicas (%)'],
            ['setting_key' => 'platform_fee_social', 'setting_value' => '2', 'setting_group' => 'payments', 'setting_type' => 'float', 'description' => 'Taxa para campanhas sociais (%)'],
            ['setting_key' => 'platform_fee_other', 'setting_value' => '2', 'setting_group' => 'payments', 'setting_type' => 'float', 'description' => 'Taxa para outras campanhas (%)'],

            // === PAGAMENTOS - LIMITES ===
            ['setting_key' => 'min_donation', 'setting_value' => '5', 'setting_group' => 'payments', 'setting_type' => 'float', 'description' => 'Doação mínima (R$)'],
            ['setting_key' => 'max_donation', 'setting_value' => '50000', 'setting_group' => 'payments', 'setting_type' => 'float', 'description' => 'Doação máxima (R$)'],

            // === CAMPANHAS ===
            ['setting_key' => 'require_approval', 'setting_value' => '1', 'setting_group' => 'campaigns', 'setting_type' => 'bool', 'description' => 'Exigir aprovação admin'],
            ['setting_key' => 'max_campaign_days', 'setting_value' => '90', 'setting_group' => 'campaigns', 'setting_type' => 'int', 'description' => 'Duração máxima (dias)'],
            ['setting_key' => 'min_goal', 'setting_value' => '100', 'setting_group' => 'campaigns', 'setting_type' => 'float', 'description' => 'Meta mínima (R$)'],
            ['setting_key' => 'max_goal', 'setting_value' => '1000000', 'setting_group' => 'campaigns', 'setting_type' => 'float', 'description' => 'Meta máxima (R$)'],
            ['setting_key' => 'allow_flexible_goal', 'setting_value' => '1', 'setting_group' => 'campaigns', 'setting_type' => 'bool', 'description' => 'Permitir meta flexível'],
            ['setting_key' => 'max_images_per_campaign', 'setting_value' => '10', 'setting_group' => 'campaigns', 'setting_type' => 'int', 'description' => 'Máximo de imagens'],

            // === RIFAS ===
            ['setting_key' => 'raffles_enabled', 'setting_value' => '1', 'setting_group' => 'raffles', 'setting_type' => 'bool', 'description' => 'Sistema de rifas ativo'],
            ['setting_key' => 'raffle_min_numbers', 'setting_value' => '100', 'setting_group' => 'raffles', 'setting_type' => 'int', 'description' => 'Mínimo de números'],
            ['setting_key' => 'raffle_max_numbers', 'setting_value' => '1000000', 'setting_group' => 'raffles', 'setting_type' => 'int', 'description' => 'Máximo de números'],
            ['setting_key' => 'raffle_min_price', 'setting_value' => '0.10', 'setting_group' => 'raffles', 'setting_type' => 'float', 'description' => 'Preço mínimo por número (R$)'],
            ['setting_key' => 'raffle_platform_fee', 'setting_value' => '10', 'setting_group' => 'raffles', 'setting_type' => 'float', 'description' => 'Taxa da plataforma em rifas (%)'],
            ['setting_key' => 'raffle_payment_timeout', 'setting_value' => '30', 'setting_group' => 'raffles', 'setting_type' => 'int', 'description' => 'Timeout de pagamento (minutos)'],

            // === NOTIFICAÇÕES ===
            ['setting_key' => 'email_notifications', 'setting_value' => '1', 'setting_group' => 'notifications', 'setting_type' => 'bool', 'description' => 'Notificações por email'],
            ['setting_key' => 'push_notifications', 'setting_value' => '1', 'setting_group' => 'notifications', 'setting_type' => 'bool', 'description' => 'Notificações push'],
            ['setting_key' => 'admin_email', 'setting_value' => 'admin@doarfazbem.com.br', 'setting_group' => 'notifications', 'setting_type' => 'string', 'description' => 'Email do admin'],
            ['setting_key' => 'notify_new_campaign', 'setting_value' => '1', 'setting_group' => 'notifications', 'setting_type' => 'bool', 'description' => 'Notificar novas campanhas'],
            ['setting_key' => 'notify_new_donation', 'setting_value' => '0', 'setting_group' => 'notifications', 'setting_type' => 'bool', 'description' => 'Notificar novas doações'],
            ['setting_key' => 'notify_new_user', 'setting_value' => '0', 'setting_group' => 'notifications', 'setting_type' => 'bool', 'description' => 'Notificar novos usuários'],

            // === SEGURANÇA ===
            ['setting_key' => 'recaptcha_enabled', 'setting_value' => '0', 'setting_group' => 'security', 'setting_type' => 'bool', 'description' => 'reCAPTCHA ativo'],
            ['setting_key' => 'recaptcha_threshold', 'setting_value' => '0.5', 'setting_group' => 'security', 'setting_type' => 'float', 'description' => 'Threshold reCAPTCHA'],
            ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'setting_group' => 'security', 'setting_type' => 'int', 'description' => 'Máximo tentativas login'],
            ['setting_key' => 'lockout_time', 'setting_value' => '15', 'setting_group' => 'security', 'setting_type' => 'int', 'description' => 'Tempo bloqueio (minutos)'],

            // === BACKUP ===
            ['setting_key' => 'backup_keep_local', 'setting_value' => '3', 'setting_group' => 'backup', 'setting_type' => 'int', 'description' => 'Backups locais a manter'],
            ['setting_key' => 'backup_keep_remote', 'setting_value' => '7', 'setting_group' => 'backup', 'setting_type' => 'int', 'description' => 'Backups remotos a manter'],
            ['setting_key' => 'backup_notification_email', 'setting_value' => '', 'setting_group' => 'backup', 'setting_type' => 'string', 'description' => 'Email notificação backup'],
            ['setting_key' => 'backup_gdrive_folder', 'setting_value' => '', 'setting_group' => 'backup', 'setting_type' => 'string', 'description' => 'ID pasta Google Drive'],

            // === APARÊNCIA ===
            ['setting_key' => 'primary_color', 'setting_value' => '#667eea', 'setting_group' => 'appearance', 'setting_type' => 'string', 'description' => 'Cor primária'],
            ['setting_key' => 'secondary_color', 'setting_value' => '#764ba2', 'setting_group' => 'appearance', 'setting_type' => 'string', 'description' => 'Cor secundária'],
            ['setting_key' => 'logo_url', 'setting_value' => '', 'setting_group' => 'appearance', 'setting_type' => 'string', 'description' => 'URL do logo'],
            ['setting_key' => 'favicon_url', 'setting_value' => '', 'setting_group' => 'appearance', 'setting_type' => 'string', 'description' => 'URL do favicon'],

            // === SEO ===
            ['setting_key' => 'meta_title', 'setting_value' => 'DoarFazBem - Plataforma de Crowdfunding', 'setting_group' => 'seo', 'setting_type' => 'string', 'description' => 'Meta título padrão'],
            ['setting_key' => 'meta_description', 'setting_value' => 'Ajude causas importantes através de doações e campanhas de crowdfunding.', 'setting_group' => 'seo', 'setting_type' => 'string', 'description' => 'Meta descrição padrão'],
            ['setting_key' => 'google_analytics_id', 'setting_value' => 'G-9SWBDMBQL6', 'setting_group' => 'seo', 'setting_type' => 'string', 'description' => 'Google Analytics ID'],

            // === SOCIAL ===
            ['setting_key' => 'facebook_url', 'setting_value' => '', 'setting_group' => 'social', 'setting_type' => 'string', 'description' => 'URL Facebook'],
            ['setting_key' => 'instagram_url', 'setting_value' => '', 'setting_group' => 'social', 'setting_type' => 'string', 'description' => 'URL Instagram'],
            ['setting_key' => 'twitter_url', 'setting_value' => '', 'setting_group' => 'social', 'setting_type' => 'string', 'description' => 'URL Twitter/X'],
            ['setting_key' => 'whatsapp_number', 'setting_value' => '', 'setting_group' => 'social', 'setting_type' => 'string', 'description' => 'Número WhatsApp'],
        ];

        foreach ($settings as $setting) {
            $setting['updated_at'] = date('Y-m-d H:i:s');
            $db->table('settings')->insert($setting);
        }
    }
}
