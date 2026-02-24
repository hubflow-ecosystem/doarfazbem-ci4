<?php
/**
 * Script para criar tabelas das funcionalidades Kickante
 */

// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsPath = FCPATH . 'app/Config/Paths.php';
require $pathsPath;
$paths = new Config\Paths();

require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';
$app = \CodeIgniter\Boot::bootWeb($paths);
$app->initialize();

$db = \Config\Database::connect();
$forge = \Config\Database::forge();

echo "Configurando funcionalidades Kickante...\n\n";

// 1. Criar tabela campaign_rewards
echo "1. Criando tabela campaign_rewards...\n";
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
    echo "   ✓ Tabela campaign_rewards criada!\n";
} else {
    echo "   - Tabela campaign_rewards já existe.\n";
}

// 2. Criar tabela campaign_media
echo "2. Criando tabela campaign_media...\n";
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
    echo "   ✓ Tabela campaign_media criada!\n";
} else {
    echo "   - Tabela campaign_media já existe.\n";
}

// 3. Adicionar coluna highlights em campaigns
echo "3. Adicionando coluna highlights em campaigns...\n";
$fields = $db->getFieldNames('campaigns');
if (!in_array('highlights', $fields)) {
    $forge->addColumn('campaigns', [
        'highlights' => [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'description',
        ],
    ]);
    echo "   ✓ Coluna highlights adicionada!\n";
} else {
    echo "   - Coluna highlights já existe.\n";
}

// 4. Adicionar coluna reward_id em donations
echo "4. Adicionando coluna reward_id em donations...\n";
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
    echo "   ✓ Coluna reward_id adicionada!\n";
} else {
    echo "   - Coluna reward_id já existe.\n";
}

echo "\n✓ Configuração concluída!\n";
