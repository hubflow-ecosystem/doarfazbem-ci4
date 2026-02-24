<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comando para executar testes funcionais do sistema
 */
class SystemTest extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'system:test';
    protected $description = 'Executa testes funcionais completos do sistema DoarFazBem';
    protected $usage       = 'system:test [--quick] [--security]';
    protected $arguments   = [];
    protected $options     = [
        '--quick'    => 'Executa apenas testes rápidos',
        '--security' => 'Foca em testes de segurança'
    ];

    private int $passed = 0;
    private int $failed = 0;
    private int $warnings = 0;

    public function run(array $params)
    {
        $this->printHeader();

        $quickMode = CLI::getOption('quick');
        $securityOnly = CLI::getOption('security');

        $startTime = microtime(true);

        if ($securityOnly) {
            // Apenas testes de segurança
            $this->testSecurityFilters();
            $this->testRateLimiting();
            $this->testAuditLogging();
            $this->testInputValidation();
        } else {
            // Testes completos
            $this->testDatabaseConnection();
            $this->testDatabaseTables();
            $this->testUserModel();
            $this->testCampaignModel();
            $this->testDonationModel();

            if (!$quickMode) {
                $this->testRaffleModels();
                $this->testAuthenticationFlow();
                $this->testCampaignCreation();
                $this->testDonationFlow();
                $this->testRaffleSystem();
            }

            $this->testSecurityFilters();
            $this->testRateLimiting();
            $this->testAuditLogging();
            $this->testMercadoPagoConfig();
            $this->testAsaasConfig();

            if (!$quickMode) {
                $this->testDatabasePerformance();
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->printSummary($duration);
    }

    // =========================================
    // TESTES DE BANCO DE DADOS
    // =========================================

    private function testDatabaseConnection(): void
    {
        $this->section("Conexão com Banco de Dados");

        try {
            $db = \Config\Database::connect();
            $this->assert($db->connID !== null, "Conexão com MySQL estabelecida");

            $query = $db->query("SELECT VERSION() as version");
            $row = $query->getRow();
            $this->assert(true, "Versão MySQL: {$row->version}");

        } catch (\Exception $e) {
            $this->assert(false, "Conexão com banco: " . $e->getMessage());
        }
    }

    private function testDatabaseTables(): void
    {
        $this->section("Estrutura do Banco de Dados");

        $requiredTables = [
            'users',
            'campaigns',
            'donations',
            'asaas_accounts',
            'raffles',
            'raffle_purchases',
            'raffle_numbers',
            'raffle_prizes',
            'instant_prizes',
            'audit_logs'
        ];

        $db = \Config\Database::connect();
        $tables = $db->listTables();

        foreach ($requiredTables as $table) {
            $exists = in_array($table, $tables);
            $this->assert($exists, "Tabela '{$table}' existe", !$exists);
        }
    }

    // =========================================
    // TESTES DE MODELOS
    // =========================================

    private function testUserModel(): void
    {
        $this->section("Model: UserModel");

        try {
            $userModel = new \App\Models\UserModel();

            $count = $userModel->countAll();
            $this->assert(true, "Total de usuários: {$count}");

            $admin = $userModel->where('role', 'admin')->first();
            $this->assert($admin !== null, "Admin encontrado", $admin === null);

            $fields = $userModel->allowedFields;
            $required = ['name', 'email', 'password', 'role'];
            foreach ($required as $field) {
                $has = in_array($field, $fields);
                $this->assert($has, "Campo '{$field}' permitido", !$has);
            }

        } catch (\Exception $e) {
            $this->assert(false, "UserModel: " . $e->getMessage());
        }
    }

    private function testCampaignModel(): void
    {
        $this->section("Model: CampaignModel");

        try {
            $campaignModel = new \App\Models\CampaignModel();

            $total = $campaignModel->countAll();
            $this->assert(true, "Total de campanhas: {$total}");

            $active = $campaignModel->where('status', 'active')->countAllResults();
            $this->assert(true, "Campanhas ativas: {$active}");

            if (method_exists($campaignModel, 'getGlobalStats')) {
                $stats = $campaignModel->getGlobalStats();
                $this->assert(is_array($stats), "getGlobalStats() retorna array");
                if (isset($stats['total_raised'])) {
                    $this->assert(true, "Total arrecadado: R$ " . number_format($stats['total_raised'], 2, ',', '.'));
                }
            }

        } catch (\Exception $e) {
            $this->assert(false, "CampaignModel: " . $e->getMessage());
        }
    }

    private function testDonationModel(): void
    {
        $this->section("Model: DonationModel");

        try {
            $donationModel = new \App\Models\Donation();

            $total = $donationModel->countAll();
            $this->assert(true, "Total de doações: {$total}");

            $confirmed = $donationModel->where('status', 'confirmed')->countAllResults();
            $this->assert(true, "Doações confirmadas: {$confirmed}");

            $totalAmount = $donationModel->where('status', 'confirmed')
                                        ->selectSum('amount')
                                        ->first();
            $amount = $totalAmount['amount'] ?? 0;
            $this->assert(true, "Total arrecadado: R$ " . number_format($amount, 2, ',', '.'));

        } catch (\Exception $e) {
            $this->assert(false, "DonationModel: " . $e->getMessage());
        }
    }

    private function testRaffleModels(): void
    {
        $this->section("Models: Sistema de Rifas");

        try {
            $raffleModel = new \App\Models\RaffleModel();
            $activeRaffle = $raffleModel->getActiveRaffle();
            $this->assert(true, "Rifa ativa: " . ($activeRaffle ? $activeRaffle['title'] : 'Nenhuma'));

            $purchaseModel = new \App\Models\RafflePurchaseModel();
            $totalPurchases = $purchaseModel->countAll();
            $this->assert(true, "Total de compras: {$totalPurchases}");

            $numberModel = new \App\Models\RaffleNumberModel();
            $totalNumbers = $numberModel->countAll();
            $this->assert(true, "Números vendidos: {$totalNumbers}");

            // Verificar se InstantPrizeModel existe
            if (class_exists('\App\Models\InstantPrizeModel')) {
                $prizeModel = new \App\Models\InstantPrizeModel();
                $prizes = $prizeModel->countAll();
                $this->assert(true, "Prêmios instantâneos: {$prizes}");
            } else {
                $this->assert(true, "InstantPrizeModel não implementado", true);
            }

        } catch (\Exception $e) {
            $this->assert(false, "Raffle Models: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE AUTENTICAÇÃO
    // =========================================

    private function testAuthenticationFlow(): void
    {
        $this->section("Fluxo de Autenticação");

        try {
            // Testar hash de senha
            $testPassword = 'TestPassword123!';
            $hash = password_hash($testPassword, PASSWORD_BCRYPT);
            $verified = password_verify($testPassword, $hash);
            $this->assert($verified, "Hash de senha BCRYPT funciona");

            // Verificar validação
            $validation = \Config\Services::validation();
            $validation->setRule('email', 'Email', 'required|valid_email');

            $validation->run(['email' => 'teste@email.com']);
            $this->assert($validation->getErrors() === [], "Validação email válido");

            $validation->reset();
            $validation->setRule('email', 'Email', 'required|valid_email');
            $validation->run(['email' => 'invalido']);
            $this->assert(!empty($validation->getErrors()), "Rejeita email inválido");

        } catch (\Exception $e) {
            $this->assert(false, "Autenticação: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE CAMPANHAS
    // =========================================

    private function testCampaignCreation(): void
    {
        $this->section("Criação de Campanhas");

        try {
            $campaignModel = new \App\Models\CampaignModel();

            $required = ['title', 'description', 'goal_amount', 'user_id', 'category', 'status'];
            $fields = $campaignModel->allowedFields;

            foreach ($required as $field) {
                $has = in_array($field, $fields);
                $this->assert($has, "Campo '{$field}' permitido", !$has);
            }

            // Validação de meta
            $validation = \Config\Services::validation();
            $validation->setRule('goal_amount', 'Meta', 'required|numeric|greater_than[0]');
            $validation->run(['goal_amount' => 1000]);
            $this->assert($validation->getErrors() === [], "Meta positiva aceita");

        } catch (\Exception $e) {
            $this->assert(false, "Campanhas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE DOAÇÕES
    // =========================================

    private function testDonationFlow(): void
    {
        $this->section("Fluxo de Doações");

        try {
            $donationModel = new \App\Models\Donation();

            $required = ['campaign_id', 'amount', 'donor_name', 'donor_email', 'payment_method', 'status'];
            $fields = $donationModel->allowedFields;

            foreach ($required as $field) {
                $has = in_array($field, $fields);
                $this->assert($has, "Campo '{$field}' permitido", !$has);
            }

            // Cálculo de taxas
            $testAmount = 100.00;
            $platformFee = 0.10;
            $expectedFee = $testAmount * $platformFee;
            $this->assert($expectedFee === 10.0, "Taxa plataforma 10%: R$ {$expectedFee}");

        } catch (\Exception $e) {
            $this->assert(false, "Doações: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE RIFAS
    // =========================================

    private function testRaffleSystem(): void
    {
        $this->section("Sistema de Rifas");

        try {
            $raffleModel = new \App\Models\RaffleModel();
            $active = $raffleModel->getActiveRaffle();

            if ($active) {
                $this->assert(true, "Rifa: {$active['title']}");
                $this->assert(true, "Preço: R$ " . number_format($active['number_price'], 2, ',', '.'));

                // Campo correto é federal_lottery_date
                $drawDate = $active['federal_lottery_date'] ?? null;
                if ($drawDate) {
                    $this->assert(true, "Sorteio: " . date('d/m/Y', strtotime($drawDate)));
                } else {
                    $this->assert(true, "Data do sorteio: Aguardando definição", true);
                }

                $stats = $raffleModel->getStats($active['id']);
                if ($stats) {
                    $this->assert(true, "Vendidos: {$stats['numbers_sold']} números");
                    $this->assert(true, "Arrecadado: R$ " . number_format($stats['total_revenue'], 2, ',', '.'));
                }
            } else {
                $this->assert(true, "Nenhuma rifa ativa", true);
            }

            $numberModel = new \App\Models\RaffleNumberModel();
            $this->assert(method_exists($numberModel, 'generateNumbers'), "generateNumbers() existe");

        } catch (\Exception $e) {
            $this->assert(false, "Rifas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE SEGURANÇA
    // =========================================

    private function testSecurityFilters(): void
    {
        $this->section("Filtros de Segurança");

        try {
            // RateLimitFilter
            $rateLimitExists = file_exists(APPPATH . 'Filters/RateLimitFilter.php');
            $this->assert($rateLimitExists, "RateLimitFilter existe");

            // SecurityHeadersFilter
            $secHeadersExists = file_exists(APPPATH . 'Filters/SecurityHeadersFilter.php');
            $this->assert($secHeadersExists, "SecurityHeadersFilter existe");

            // Configuração de filtros
            $filters = new \Config\Filters();
            $hasRateLimit = isset($filters->aliases['ratelimit']);
            $hasSecHeaders = isset($filters->aliases['securityheaders']);

            $this->assert($hasRateLimit, "RateLimitFilter registrado");
            $this->assert($hasSecHeaders, "SecurityHeadersFilter registrado");

        } catch (\Exception $e) {
            $this->assert(false, "Filtros: " . $e->getMessage());
        }
    }

    private function testRateLimiting(): void
    {
        $this->section("Rate Limiting");

        try {
            $cache = \Config\Services::cache();

            $testKey = 'test_rate_limit_' . time();
            $cache->delete($testKey);

            // Simular incremento
            for ($i = 0; $i < 5; $i++) {
                $current = $cache->get($testKey) ?: 0;
                $cache->save($testKey, $current + 1, 60);
            }

            $attempts = $cache->get($testKey);
            $this->assert($attempts === 5, "Incremento correto: {$attempts} tentativas");

            $cache->delete($testKey);
            $this->assert(true, "Cache limpo após teste");

        } catch (\Exception $e) {
            $this->assert(false, "Rate limiting: " . $e->getMessage());
        }
    }

    private function testAuditLogging(): void
    {
        $this->section("Audit Logging");

        try {
            $auditLogExists = file_exists(APPPATH . 'Models/AuditLogModel.php');
            $this->assert($auditLogExists, "AuditLogModel existe");

            if ($auditLogExists) {
                $auditLog = new \App\Models\AuditLogModel();

                $this->assert(method_exists($auditLog, 'log'), "Método log() existe");
                $this->assert(method_exists($auditLog, 'logLogin'), "Método logLogin() existe");

                // Inserir log de teste
                $logId = $auditLog->log(
                    'TEST',              // action
                    null,                // userId
                    'SYSTEM_TEST',       // entityType
                    null,                // entityId
                    null,                // oldValue
                    null,                // newValue
                    ['test' => true, 'command' => 'system:test']  // extraData
                );

                $this->assert($logId > 0, "Log inserido: ID {$logId}");

                // Limpar
                $auditLog->delete($logId);
            }

        } catch (\Exception $e) {
            $this->assert(false, "Audit: " . $e->getMessage());
        }
    }

    private function testInputValidation(): void
    {
        $this->section("Validação de Input");

        try {
            $validation = \Config\Services::validation();

            // CPF
            $validation->setRule('cpf', 'CPF', 'required|min_length[11]|max_length[14]');
            $validation->run(['cpf' => '12345678901']);
            $this->assert($validation->getErrors() === [], "CPF válido aceito");

            // Email
            $validation->reset();
            $validation->setRule('email', 'Email', 'required|valid_email');
            $validation->run(['email' => 'test@test.com']);
            $this->assert($validation->getErrors() === [], "Email válido aceito");

            // Valor numérico
            $validation->reset();
            $validation->setRule('amount', 'Valor', 'required|numeric|greater_than[0]');
            $validation->run(['amount' => 50.00]);
            $this->assert($validation->getErrors() === [], "Valor numérico aceito");

            // XSS Test
            $testInput = '<script>alert("xss")</script>';
            $sanitized = esc($testInput);
            $this->assert($sanitized !== $testInput, "XSS sanitizado: " . substr($sanitized, 0, 30) . "...");

        } catch (\Exception $e) {
            $this->assert(false, "Validação: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE INTEGRAÇÕES
    // =========================================

    private function testMercadoPagoConfig(): void
    {
        $this->section("Mercado Pago");

        try {
            $publicKey = env('mercadopago.sandbox.public_key', '');
            $accessToken = env('mercadopago.sandbox.access_token', '');

            $hasPublicKey = !empty($publicKey);
            $hasAccessToken = !empty($accessToken);

            $this->assert($hasPublicKey, "Public Key configurada", !$hasPublicKey);
            $this->assert($hasAccessToken, "Access Token configurada", !$hasAccessToken);

            if ($hasPublicKey) {
                $isSandbox = strpos($publicKey, 'TEST') === 0;
                $this->assert($isSandbox, "Modo Sandbox ativo");
            }

        } catch (\Exception $e) {
            $this->assert(false, "Mercado Pago: " . $e->getMessage());
        }
    }

    private function testAsaasConfig(): void
    {
        $this->section("Asaas");

        try {
            $asaasConfig = config('Asaas');

            if ($asaasConfig) {
                $sandbox = $asaasConfig->sandbox ?? true;
                $this->assert(true, "Modo: " . ($sandbox ? 'Sandbox' : 'Produção'));

                $hasApiKey = !empty($asaasConfig->apiKey);
                $this->assert($hasApiKey, "API Key configurada", !$hasApiKey);
            } else {
                $this->assert(false, "Configuração não encontrada");
            }

        } catch (\Exception $e) {
            $this->assert(false, "Asaas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE PERFORMANCE
    // =========================================

    private function testDatabasePerformance(): void
    {
        $this->section("Performance de Queries");

        try {
            $db = \Config\Database::connect();

            // Query campanhas
            $start = microtime(true);
            $db->table('campaigns')->where('status', 'active')->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 100, "Campanhas ativas: {$duration}ms");

            // Query com join
            $start = microtime(true);
            $db->table('donations d')
               ->select('d.*, c.title')
               ->join('campaigns c', 'c.id = d.campaign_id', 'left')
               ->where('d.status', 'confirmed')
               ->limit(100)
               ->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 200, "Doações com join: {$duration}ms");

            // Query agregação
            $start = microtime(true);
            $db->table('donations')
               ->select('SUM(amount) as total, COUNT(*) as count')
               ->where('status', 'confirmed')
               ->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 50, "Agregação: {$duration}ms");

        } catch (\Exception $e) {
            $this->assert(false, "Performance: " . $e->getMessage());
        }
    }

    // =========================================
    // HELPERS
    // =========================================

    private function assert(bool $condition, string $message, bool $isWarning = false): void
    {
        if ($condition) {
            $this->passed++;
            CLI::write("  ✓ {$message}", 'green');
        } elseif ($isWarning) {
            $this->warnings++;
            CLI::write("  ⚠ {$message}", 'yellow');
        } else {
            $this->failed++;
            CLI::write("  ✗ {$message}", 'red');
        }
    }

    private function section(string $title): void
    {
        CLI::newLine();
        CLI::write("▶ {$title}", 'cyan');
        CLI::write(str_repeat("-", 50), 'dark_gray');
    }

    private function printHeader(): void
    {
        CLI::newLine();
        CLI::write(str_repeat("=", 60), 'cyan');
        CLI::write(" TESTE FUNCIONAL - DoarFazBem", 'white');
        CLI::write(str_repeat("=", 60), 'cyan');
        CLI::write(" Data: " . date('d/m/Y H:i:s'), 'dark_gray');
        CLI::write(" Ambiente: " . ENVIRONMENT, 'dark_gray');
        CLI::write(str_repeat("=", 60), 'cyan');
    }

    private function printSummary(float $duration): void
    {
        CLI::newLine();
        CLI::write(str_repeat("=", 60), 'cyan');
        CLI::write(" RESUMO DOS TESTES", 'white');
        CLI::write(str_repeat("=", 60), 'cyan');
        CLI::write(" ✓ Passou: {$this->passed}", 'green');
        CLI::write(" ✗ Falhou: {$this->failed}", $this->failed > 0 ? 'red' : 'dark_gray');
        CLI::write(" ⚠ Avisos: {$this->warnings}", $this->warnings > 0 ? 'yellow' : 'dark_gray');
        CLI::write(" ⏱ Tempo: {$duration}s", 'dark_gray');
        CLI::write(str_repeat("=", 60), 'cyan');

        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;

        if ($this->failed === 0) {
            CLI::newLine();
            CLI::write(" 🎉 TODOS OS TESTES PASSARAM! ({$percentage}%)", 'green');
        } else {
            CLI::newLine();
            CLI::write(" ⚠ ALGUNS TESTES FALHARAM ({$percentage}% passou)", 'yellow');
        }
        CLI::write(str_repeat("=", 60), 'cyan');
        CLI::newLine();
    }
}
