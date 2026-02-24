<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupKickanteFeatures extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'kickante:setup';
    protected $description = 'Configura tabelas para funcionalidades estilo Kickante';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        CLI::write("Configurando funcionalidades Kickante...\n", 'yellow');

        // 1. Criar tabela campaign_rewards
        CLI::write("1. Criando tabela campaign_rewards...", 'white');
        if (!$db->tableExists('campaign_rewards')) {
            $forge->addField([
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
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                ],
                'min_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                ],
                'max_quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'claimed_quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'delivery_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'image' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                    'null' => true,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'sort_order' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
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
            $forge->addKey('id', true);
            $forge->addKey('campaign_id');
            $forge->addKey('min_amount');
            $forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
            $forge->createTable('campaign_rewards');
            CLI::write("   Tabela campaign_rewards criada!", 'green');
        } else {
            CLI::write("   Tabela campaign_rewards já existe.", 'light_gray');
        }

        // 2. Criar tabela campaign_media
        CLI::write("2. Criando tabela campaign_media...", 'white');
        if (!$db->tableExists('campaign_media')) {
            $forge->addField([
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
                'type' => [
                    'type' => 'ENUM',
                    'constraint' => ['image', 'video'],
                    'default' => 'image',
                ],
                'url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                ],
                'thumbnail' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                    'null' => true,
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'sort_order' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'is_primary' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('campaign_id');
            $forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
            $forge->createTable('campaign_media');
            CLI::write("   Tabela campaign_media criada!", 'green');
        } else {
            CLI::write("   Tabela campaign_media já existe.", 'light_gray');
        }

        // 3. Adicionar coluna highlights em campaigns
        CLI::write("3. Adicionando coluna highlights em campaigns...", 'white');
        $fields = $db->getFieldNames('campaigns');
        if (!in_array('highlights', $fields)) {
            $forge->addColumn('campaigns', [
                'highlights' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'description',
                ],
            ]);
            CLI::write("   Coluna highlights adicionada!", 'green');
        } else {
            CLI::write("   Coluna highlights já existe.", 'light_gray');
        }

        // 4. Adicionar coluna reward_id em donations
        CLI::write("4. Adicionando coluna reward_id em donations...", 'white');
        $fields = $db->getFieldNames('donations');
        if (!in_array('reward_id', $fields)) {
            $forge->addColumn('donations', [
                'reward_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'campaign_id',
                ],
            ]);
            CLI::write("   Coluna reward_id adicionada!", 'green');
        } else {
            CLI::write("   Coluna reward_id já existe.", 'light_gray');
        }

        CLI::write("\nConfiguração concluída!", 'green');
    }
}
