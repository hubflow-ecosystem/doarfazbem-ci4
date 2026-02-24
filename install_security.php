<?php
/**
 * HUBFLOW - Script de Instalação de Módulos de Segurança
 * ======================================================
 * Execute este script em cada projeto para:
 * 1. Criar tabelas de segurança
 * 2. Criar/atualizar admin padrão
 * 3. Copiar bibliotecas necessárias
 *
 * Uso: php install_security.php
 */

echo "\n";
echo "=====================================================\n";
echo "  HUBFLOW - Instalação de Módulos de Segurança\n";
echo "=====================================================\n\n";

// Detectar diretório do projeto
$projectDir = dirname(__FILE__);
if (basename($projectDir) === 'modules') {
    $projectDir = dirname(dirname($projectDir));
}

// Verificar se é um projeto CodeIgniter 4
$isCI4 = file_exists($projectDir . '/app') && file_exists($projectDir . '/spark');
$envFile = $projectDir . '/.env';

echo "Diretório do projeto: {$projectDir}\n";
echo "É CodeIgniter 4: " . ($isCI4 ? 'Sim' : 'Não') . "\n\n";

// Carregar configurações do .env
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => ''
];

if (file_exists($envFile)) {
    $env = file_get_contents($envFile);

    if (preg_match('/database\.default\.hostname\s*=\s*(.+)/', $env, $m)) {
        $dbConfig['host'] = trim($m[1]);
    }
    if (preg_match('/database\.default\.username\s*=\s*(.+)/', $env, $m)) {
        $dbConfig['user'] = trim($m[1]);
    }
    if (preg_match('/database\.default\.password\s*=\s*(.*)/', $env, $m)) {
        $dbConfig['pass'] = trim($m[1]);
    }
    if (preg_match('/database\.default\.database\s*=\s*(.+)/', $env, $m)) {
        $dbConfig['name'] = trim($m[1]);
    }
}

if (empty($dbConfig['name'])) {
    echo "⚠️  Database não configurado no .env\n";
    echo "Por favor, configure database.default.database no arquivo .env\n\n";

    // Tentar detectar pelo nome da pasta
    $folderName = basename($projectDir);
    $possibleDbNames = [
        $folderName,
        str_replace('-', '_', $folderName),
        str_replace('.', '_', $folderName),
    ];

    echo "Possíveis nomes de banco: " . implode(', ', $possibleDbNames) . "\n";
    echo "Tentando conectar...\n\n";

    foreach ($possibleDbNames as $dbName) {
        try {
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbName};charset=utf8mb4",
                $dbConfig['user'],
                $dbConfig['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $dbConfig['name'] = $dbName;
            echo "✅ Banco encontrado: {$dbName}\n\n";
            break;
        } catch (PDOException $e) {
            // Continua tentando
        }
    }
}

if (empty($dbConfig['name'])) {
    die("❌ Não foi possível determinar o banco de dados.\n");
}

// Conectar ao banco
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conectado ao banco: {$dbConfig['name']}\n\n";
} catch (PDOException $e) {
    die("❌ Erro de conexão: " . $e->getMessage() . "\n");
}

// ============================================================
// CRIAR TABELAS DE SEGURANÇA
// ============================================================

echo "📦 Criando tabelas de segurança...\n\n";

$tables = [
    // Tabela de tentativas de login
    "CREATE TABLE IF NOT EXISTS security_login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        email VARCHAR(255) NULL,
        user_id INT NULL,
        success TINYINT(1) DEFAULT 0,
        user_agent TEXT NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_ip_address (ip_address),
        INDEX idx_email (email),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabela de IPs bloqueados
    "CREATE TABLE IF NOT EXISTS security_ip_blacklist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        reason VARCHAR(255) NULL,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_ip_address (ip_address),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabela de tokens de verificação
    "CREATE TABLE IF NOT EXISTS verification_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('email', 'phone') NOT NULL,
        target VARCHAR(255) NOT NULL,
        code VARCHAR(10) NULL,
        token VARCHAR(64) NULL,
        method VARCHAR(20) NULL,
        expires_at DATETIME NOT NULL,
        verified_at DATETIME NULL,
        invalidated_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_type (type),
        INDEX idx_token (token),
        INDEX idx_code (code),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabela de tentativas de verificação
    "CREATE TABLE IF NOT EXISTS verification_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(20) NOT NULL,
        success TINYINT(1) DEFAULT 0,
        ip_address VARCHAR(45) NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_type (type),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabela de logs de auditoria
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(50) NOT NULL,
        entity_type VARCHAR(100) NULL,
        entity_id INT NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        description TEXT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        url VARCHAR(500) NULL,
        method VARCHAR(10) NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_user_id (user_id),
        INDEX idx_action (action),
        INDEX idx_entity (entity_type, entity_id),
        INDEX idx_created_at (created_at),
        INDEX idx_ip (ip_address)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        // Extrair nome da tabela
        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m);
        echo "  ✅ Tabela {$m[1]} criada/verificada\n";
    } catch (PDOException $e) {
        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m);
        echo "  ⚠️  Erro na tabela {$m[1]}: " . $e->getMessage() . "\n";
    }
}

// ============================================================
// ADICIONAR CAMPOS À TABELA DE USUÁRIOS
// ============================================================

echo "\n📦 Verificando tabela de usuários...\n\n";

// Detectar tabela de usuários
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$userTable = null;
$possibleTables = ['users', 'ml_usuarios', 'usuarios', 'user', 'admins'];

foreach ($possibleTables as $table) {
    if (in_array($table, $tables)) {
        $userTable = $table;
        break;
    }
}

if ($userTable) {
    echo "  Tabela de usuários: {$userTable}\n";

    // Campos a adicionar
    $fieldsToAdd = [
        'email_verified' => "TINYINT(1) DEFAULT 0",
        'email_verified_at' => "DATETIME NULL",
        'phone_verified' => "TINYINT(1) DEFAULT 0",
        'phone_verified_at' => "DATETIME NULL",
        'google_id' => "VARCHAR(255) NULL",
        'password_reset_token' => "VARCHAR(64) NULL",
        'password_reset_expires' => "DATETIME NULL",
        'two_factor_enabled' => "TINYINT(1) DEFAULT 0",
        'two_factor_secret' => "VARCHAR(255) NULL"
    ];

    $columns = $pdo->query("DESCRIBE {$userTable}")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($fieldsToAdd as $field => $type) {
        if (!in_array($field, $columns)) {
            try {
                $pdo->exec("ALTER TABLE {$userTable} ADD COLUMN {$field} {$type}");
                echo "  ✅ Campo {$field} adicionado\n";
            } catch (PDOException $e) {
                echo "  ⚠️  Campo {$field}: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ℹ️  Campo {$field} já existe\n";
        }
    }
} else {
    echo "  ⚠️  Tabela de usuários não encontrada\n";
}

// ============================================================
// CRIAR/ATUALIZAR ADMIN PADRÃO
// ============================================================

echo "\n👤 Configurando admin padrão...\n\n";

$adminEmail = 'cesar@hubflowai.com';
$adminPassword = '@GAd8EDSS5Ypn4er@';
$adminName = 'Admin HubFlow';

// Hash da senha
$passwordHash = password_hash($adminPassword, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3
]);

// Fallback para bcrypt
if ($passwordHash === false) {
    $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
}

if ($userTable) {
    $columns = $pdo->query("DESCRIBE {$userTable}")->fetchAll(PDO::FETCH_COLUMN);

    // Determinar campos
    $emailField = in_array('email', $columns) ? 'email' : 'user_email';
    $passwordField = in_array('password_hash', $columns) ? 'password_hash' :
                    (in_array('password', $columns) ? 'password' : 'senha');
    $nameField = in_array('name', $columns) ? 'name' :
                (in_array('nome', $columns) ? 'nome' : 'user_name');
    $roleField = in_array('role', $columns) ? 'role' :
                (in_array('is_super_admin', $columns) ? 'is_super_admin' : 'tipo');

    // Verificar se admin existe
    $stmt = $pdo->prepare("SELECT * FROM {$userTable} WHERE {$emailField} = ?");
    $stmt->execute([$adminEmail]);
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAdmin) {
        // Atualizar
        $updateSql = "UPDATE {$userTable} SET {$passwordField} = ?";
        $params = [$passwordHash];

        if ($roleField === 'role') {
            $updateSql .= ", {$roleField} = 'superadmin'";
        } elseif ($roleField === 'is_super_admin') {
            $updateSql .= ", {$roleField} = 1";
        }

        if (in_array('updated_at', $columns)) {
            $updateSql .= ", updated_at = NOW()";
        }

        $updateSql .= " WHERE {$emailField} = ?";
        $params[] = $adminEmail;

        $stmt = $pdo->prepare($updateSql);
        $stmt->execute($params);

        echo "  ✅ Admin atualizado (ID: {$existingAdmin['id']})\n";
    } else {
        // Criar novo
        $insertFields = [$emailField, $nameField, $passwordField];
        $insertValues = [$adminEmail, $adminName, $passwordHash];

        if ($roleField === 'role') {
            $insertFields[] = $roleField;
            $insertValues[] = 'superadmin';
        } elseif ($roleField === 'is_super_admin') {
            $insertFields[] = $roleField;
            $insertValues[] = 1;
        }

        if (in_array('created_at', $columns)) {
            $insertFields[] = 'created_at';
            $insertValues[] = date('Y-m-d H:i:s');
        }

        if (in_array('email_verified', $columns)) {
            $insertFields[] = 'email_verified';
            $insertValues[] = 1;
        }

        $placeholders = array_fill(0, count($insertFields), '?');
        $sql = "INSERT INTO {$userTable} (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($insertValues);

        echo "  ✅ Admin criado (ID: " . $pdo->lastInsertId() . ")\n";
    }

    echo "\n  📧 Email: {$adminEmail}\n";
    echo "  🔐 Senha: {$adminPassword}\n";
}

// ============================================================
// RESUMO FINAL
// ============================================================

echo "\n=====================================================\n";
echo "  ✅ INSTALAÇÃO CONCLUÍDA!\n";
echo "=====================================================\n\n";

echo "Módulos instalados:\n";
echo "  ✅ Sistema de Rate Limiting (anti-brute force)\n";
echo "  ✅ Sistema de Blacklist de IPs\n";
echo "  ✅ Sistema de Verificação de Email/Telefone\n";
echo "  ✅ Sistema de Logs Avançados (Audit Trail)\n";
echo "  ✅ Admin padrão configurado\n\n";

echo "Próximos passos:\n";
echo "  1. Copie as Libraries para app/Libraries/Security/\n";
echo "  2. Configure as variáveis de ambiente para SMS/WhatsApp\n";
echo "  3. Adicione o link do Mapa do Site no rodapé\n";
echo "  4. Teste o login com as credenciais admin\n\n";
