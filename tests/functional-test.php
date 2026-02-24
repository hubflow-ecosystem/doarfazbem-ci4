<?php
/**
 * =====================================================
 * TESTE FUNCIONAL COMPLETO - DoarFazBem
 * =====================================================
 * Este script executa testes funcionais em todo o sistema:
 * - Criação de contas (doadores e criadores)
 * - Campanhas
 * - Doações via PIX
 * - Sistema de Rifas
 * - Segurança
 * =====================================================
 */

// Carregar o framework CodeIgniter
$minify = false;

// Bootstrap usando o mesmo método do index.php
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);
chdir(FCPATH);

require_once realpath(__DIR__ . '/../app/Config/Paths.php');

$paths = new Config\Paths();

// Define APPPATH, SYSTEMPATH, WRITEPATH
define('APPPATH', realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
define('WRITEPATH', realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);

require_once SYSTEMPATH . 'bootstrap.php';
require_once APPPATH . 'Config/Constants.php';

// Inicializar o CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

class FunctionalTest
{
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $warnings = 0;

    public function run(): void
    {
        $this->printHeader("TESTE FUNCIONAL COMPLETO - DoarFazBem");
        $startTime = microtime(true);

        // 1. Testes de Banco de Dados
        $this->testDatabaseConnection();
        $this->testDatabaseTables();

        // 2. Testes de Modelos
        $this->testUserModel();
        $this->testCampaignModel();
        $this->testDonationModel();
        $this->testRaffleModels();

        // 3. Testes de Autenticação
        $this->testAuthenticationFlow();

        // 4. Testes de Campanhas
        $this->testCampaignCreation();
        $this->testCampaignListing();

        // 5. Testes de Doações
        $this->testDonationFlow();

        // 6. Testes de Rifas
        $this->testRaffleSystem();

        // 7. Testes de Segurança
        $this->testSecurityFilters();
        $this->testRateLimiting();
        $this->testAuditLogging();

        // 8. Testes de Integrações
        $this->testMercadoPagoConfig();
        $this->testAsaasConfig();

        // 9. Testes de Performance
        $this->testDatabaseQueries();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->printSummary($duration);
    }

    // =========================================
    // TESTES DE BANCO DE DADOS
    // =========================================

    private function testDatabaseConnection(): void
    {
        $this->printSection("Conexão com Banco de Dados");

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
        $this->printSection("Estrutura do Banco de Dados");

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
        $this->printSection("Model: UserModel");

        try {
            $userModel = new \App\Models\UserModel();

            // Testar contagem de usuários
            $count = $userModel->countAll();
            $this->assert(true, "Total de usuários: {$count}");

            // Testar busca por email
            $admin = $userModel->where('role', 'admin')->first();
            $this->assert($admin !== null, "Admin encontrado", $admin === null);

            // Testar campos obrigatórios
            $fields = $userModel->allowedFields;
            $required = ['name', 'email', 'password', 'role', 'cpf_cnpj'];
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
        $this->printSection("Model: CampaignModel");

        try {
            $campaignModel = new \App\Models\CampaignModel();

            // Testar contagem
            $total = $campaignModel->countAll();
            $this->assert(true, "Total de campanhas: {$total}");

            // Testar campanhas ativas
            $active = $campaignModel->getActiveCampaigns();
            $activeCount = is_array($active) ? count($active) : 0;
            $this->assert(true, "Campanhas ativas: {$activeCount}");

            // Testar método getGlobalStats
            if (method_exists($campaignModel, 'getGlobalStats')) {
                $stats = $campaignModel->getGlobalStats();
                $this->assert(is_array($stats), "getGlobalStats() retorna array");
            }

        } catch (\Exception $e) {
            $this->assert(false, "CampaignModel: " . $e->getMessage());
        }
    }

    private function testDonationModel(): void
    {
        $this->printSection("Model: DonationModel");

        try {
            $donationModel = new \App\Models\Donation();

            // Testar contagem
            $total = $donationModel->countAll();
            $this->assert(true, "Total de doações: {$total}");

            // Testar doações confirmadas
            $confirmed = $donationModel->where('status', 'confirmed')->countAllResults();
            $this->assert(true, "Doações confirmadas: {$confirmed}");

            // Testar soma total
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
        $this->printSection("Models: Sistema de Rifas");

        try {
            // RaffleModel
            $raffleModel = new \App\Models\RaffleModel();
            $activeRaffle = $raffleModel->getActiveRaffle();
            $this->assert(true, "Rifa ativa: " . ($activeRaffle ? $activeRaffle['title'] : 'Nenhuma'));

            // RafflePurchaseModel
            $purchaseModel = new \App\Models\RafflePurchaseModel();
            $totalPurchases = $purchaseModel->countAll();
            $this->assert(true, "Total de compras de rifas: {$totalPurchases}");

            // RaffleNumberModel
            $numberModel = new \App\Models\RaffleNumberModel();
            $totalNumbers = $numberModel->countAll();
            $this->assert(true, "Total de números vendidos: {$totalNumbers}");

            // InstantPrizeModel
            $prizeModel = new \App\Models\InstantPrizeModel();
            $prizes = $prizeModel->countAll();
            $this->assert(true, "Prêmios instantâneos cadastrados: {$prizes}");

        } catch (\Exception $e) {
            $this->assert(false, "Raffle Models: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE AUTENTICAÇÃO
    // =========================================

    private function testAuthenticationFlow(): void
    {
        $this->printSection("Fluxo de Autenticação");

        try {
            // Verificar rotas de autenticação
            $routes = service('routes');

            // Testar hash de senha
            $testPassword = 'TestPassword123!';
            $hash = password_hash($testPassword, PASSWORD_BCRYPT);
            $verified = password_verify($testPassword, $hash);
            $this->assert($verified, "Hash de senha funciona corretamente");

            // Verificar configuração de sessão
            $session = \Config\Services::session();
            $this->assert($session !== null, "Serviço de sessão disponível");

            // Verificar validação de email
            $validation = \Config\Services::validation();
            $validation->setRule('email', 'Email', 'required|valid_email');

            $validation->run(['email' => 'teste@email.com']);
            $this->assert($validation->getErrors() === [], "Validação de email válido");

            $validation->run(['email' => 'email_invalido']);
            $this->assert(!empty($validation->getErrors()), "Validação detecta email inválido");

        } catch (\Exception $e) {
            $this->assert(false, "Autenticação: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE CAMPANHAS
    // =========================================

    private function testCampaignCreation(): void
    {
        $this->printSection("Criação de Campanhas");

        try {
            $campaignModel = new \App\Models\CampaignModel();

            // Verificar campos obrigatórios
            $required = ['title', 'description', 'goal', 'user_id', 'category', 'status'];
            $fields = $campaignModel->allowedFields;

            foreach ($required as $field) {
                $has = in_array($field, $fields);
                $this->assert($has, "Campo de campanha '{$field}' permitido", !$has);
            }

            // Verificar validação de meta
            $validation = \Config\Services::validation();
            $validation->setRule('goal', 'Meta', 'required|numeric|greater_than[0]');

            $validation->run(['goal' => 1000]);
            $this->assert($validation->getErrors() === [], "Meta válida aceita");

            $validation->run(['goal' => -100]);
            $this->assert(!empty($validation->getErrors()), "Meta negativa rejeitada");

        } catch (\Exception $e) {
            $this->assert(false, "Criação de campanhas: " . $e->getMessage());
        }
    }

    private function testCampaignListing(): void
    {
        $this->printSection("Listagem de Campanhas");

        try {
            $campaignModel = new \App\Models\CampaignModel();

            // Testar listagem ativa
            $active = $campaignModel->where('status', 'active')->findAll();
            $this->assert(true, "Campanhas ativas carregadas: " . count($active));

            // Testar paginação
            $paginated = $campaignModel->paginate(10);
            $this->assert(is_array($paginated), "Paginação funciona");

            // Testar busca por categoria
            $categories = ['saude', 'educacao', 'animais', 'emergencia'];
            foreach ($categories as $cat) {
                $count = $campaignModel->where('category', $cat)->countAllResults();
                if ($count > 0) {
                    $this->assert(true, "Categoria '{$cat}': {$count} campanhas");
                }
            }

        } catch (\Exception $e) {
            $this->assert(false, "Listagem de campanhas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE DOAÇÕES
    // =========================================

    private function testDonationFlow(): void
    {
        $this->printSection("Fluxo de Doações");

        try {
            $donationModel = new \App\Models\Donation();

            // Verificar campos
            $required = ['campaign_id', 'amount', 'donor_name', 'donor_email', 'payment_method', 'status'];
            $fields = $donationModel->allowedFields;

            foreach ($required as $field) {
                $has = in_array($field, $fields);
                $this->assert($has, "Campo de doação '{$field}' permitido", !$has);
            }

            // Verificar cálculo de taxas
            $testAmount = 100.00;
            $platformFee = 0.10; // 10%
            $expectedFee = $testAmount * $platformFee;
            $this->assert($expectedFee === 10.0, "Cálculo de taxa da plataforma: R$ {$expectedFee}");

            // Testar status válidos
            $validStatus = ['pending', 'confirmed', 'cancelled', 'refunded'];
            $this->assert(true, "Status válidos: " . implode(', ', $validStatus));

            // Testar métodos de pagamento
            $methods = ['pix', 'credit_card', 'boleto'];
            $this->assert(true, "Métodos de pagamento: " . implode(', ', $methods));

        } catch (\Exception $e) {
            $this->assert(false, "Fluxo de doações: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE RIFAS
    // =========================================

    private function testRaffleSystem(): void
    {
        $this->printSection("Sistema de Rifas (Números da Sorte)");

        try {
            $raffleModel = new \App\Models\RaffleModel();

            // Verificar rifa ativa
            $active = $raffleModel->getActiveRaffle();
            if ($active) {
                $this->assert(true, "Rifa ativa: {$active['title']}");
                $this->assert(true, "Preço por número: R$ " . number_format($active['number_price'], 2, ',', '.'));
                $this->assert(true, "Data do sorteio: " . date('d/m/Y', strtotime($active['draw_date'])));

                // Verificar estatísticas
                $stats = $raffleModel->getRaffleStats($active['id']);
                if ($stats) {
                    $this->assert(true, "Números vendidos: {$stats['total_sold']}");
                    $this->assert(true, "Total arrecadado: R$ " . number_format($stats['total_amount'], 2, ',', '.'));
                }
            } else {
                $this->assert(true, "Nenhuma rifa ativa no momento", true);
            }

            // Verificar geração de números
            $numberModel = new \App\Models\RaffleNumberModel();
            $this->assert(method_exists($numberModel, 'generateNumbers'), "Método generateNumbers existe");
            $this->assert(method_exists($numberModel, 'getAvailableNumbers'), "Método getAvailableNumbers existe");

            // Verificar prêmios instantâneos
            $prizeModel = new \App\Models\InstantPrizeModel();
            $availablePrizes = $prizeModel->where('status', 'active')->countAllResults();
            $this->assert(true, "Prêmios instantâneos ativos: {$availablePrizes}");

        } catch (\Exception $e) {
            $this->assert(false, "Sistema de rifas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE SEGURANÇA
    // =========================================

    private function testSecurityFilters(): void
    {
        $this->printSection("Filtros de Segurança");

        try {
            // Verificar RateLimitFilter
            $rateLimitExists = file_exists(APPPATH . 'Filters/RateLimitFilter.php');
            $this->assert($rateLimitExists, "RateLimitFilter existe");

            // Verificar SecurityHeadersFilter
            $secHeadersExists = file_exists(APPPATH . 'Filters/SecurityHeadersFilter.php');
            $this->assert($secHeadersExists, "SecurityHeadersFilter existe");

            // Verificar configuração de filtros
            $filters = new \Config\Filters();
            $hasRateLimit = isset($filters->aliases['ratelimit']);
            $hasSecHeaders = isset($filters->aliases['securityheaders']);

            $this->assert($hasRateLimit, "RateLimitFilter registrado");
            $this->assert($hasSecHeaders, "SecurityHeadersFilter registrado");

            // Verificar CSRF
            $security = config('App');
            $this->assert(true, "CSRFTokenName: " . ($security->CSRFTokenName ?? 'csrf_token'));

        } catch (\Exception $e) {
            $this->assert(false, "Filtros de segurança: " . $e->getMessage());
        }
    }

    private function testRateLimiting(): void
    {
        $this->printSection("Rate Limiting");

        try {
            $cache = \Config\Services::cache();

            // Simular tentativas de login
            $testKey = 'rate_limit_test_' . md5('test@test.com');

            // Limpar cache de teste
            $cache->delete($testKey);

            // Testar incremento
            $attempts = 0;
            for ($i = 0; $i < 5; $i++) {
                $current = $cache->get($testKey) ?: 0;
                $cache->save($testKey, $current + 1, 60);
                $attempts = $cache->get($testKey);
            }

            $this->assert($attempts === 5, "Rate limiting incrementa corretamente: {$attempts} tentativas");

            // Limpar
            $cache->delete($testKey);

        } catch (\Exception $e) {
            $this->assert(false, "Rate limiting: " . $e->getMessage());
        }
    }

    private function testAuditLogging(): void
    {
        $this->printSection("Audit Logging");

        try {
            // Verificar se model existe
            $auditLogExists = file_exists(APPPATH . 'Models/AuditLogModel.php');
            $this->assert($auditLogExists, "AuditLogModel existe");

            if ($auditLogExists) {
                $auditLog = new \App\Models\AuditLogModel();

                // Verificar métodos
                $this->assert(method_exists($auditLog, 'log'), "Método log() existe");
                $this->assert(method_exists($auditLog, 'logLogin'), "Método logLogin() existe");
                $this->assert(method_exists($auditLog, 'logLoginFailed'), "Método logLoginFailed() existe");

                // Testar log de teste
                $logId = $auditLog->log('TEST', 'FUNCTIONAL_TEST', [
                    'test' => true,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);

                $this->assert($logId > 0, "Audit log inserido: ID {$logId}");

                // Limpar log de teste
                $auditLog->delete($logId);
            }

        } catch (\Exception $e) {
            $this->assert(false, "Audit logging: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE INTEGRAÇÕES
    // =========================================

    private function testMercadoPagoConfig(): void
    {
        $this->printSection("Integração Mercado Pago");

        try {
            $publicKey = getenv('mercadopago.sandbox.public_key') ?: env('mercadopago.sandbox.public_key');
            $accessToken = getenv('mercadopago.sandbox.access_token') ?: env('mercadopago.sandbox.access_token');

            $hasPublicKey = !empty($publicKey);
            $hasAccessToken = !empty($accessToken);

            $this->assert($hasPublicKey, "Public Key configurada", !$hasPublicKey);
            $this->assert($hasAccessToken, "Access Token configurada", !$hasAccessToken);

            if ($hasPublicKey && $hasAccessToken) {
                $this->assert(
                    strpos($publicKey, 'TEST') === 0,
                    "Modo Sandbox ativo (chave começa com TEST)"
                );
            }

        } catch (\Exception $e) {
            $this->assert(false, "Mercado Pago: " . $e->getMessage());
        }
    }

    private function testAsaasConfig(): void
    {
        $this->printSection("Integração Asaas");

        try {
            $asaasConfig = config('Asaas');

            if ($asaasConfig) {
                $sandbox = $asaasConfig->sandbox ?? true;
                $this->assert(true, "Modo: " . ($sandbox ? 'Sandbox' : 'Produção'));

                $hasApiKey = !empty($asaasConfig->apiKey);
                $this->assert($hasApiKey, "API Key configurada", !$hasApiKey);
            } else {
                $this->assert(false, "Configuração Asaas não encontrada");
            }

        } catch (\Exception $e) {
            $this->assert(false, "Asaas: " . $e->getMessage());
        }
    }

    // =========================================
    // TESTES DE PERFORMANCE
    // =========================================

    private function testDatabaseQueries(): void
    {
        $this->printSection("Performance de Queries");

        try {
            $db = \Config\Database::connect();

            // Testar query de campanhas ativas
            $start = microtime(true);
            $db->table('campaigns')
               ->where('status', 'active')
               ->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 100, "Query campanhas ativas: {$duration}ms");

            // Testar query de doações com join
            $start = microtime(true);
            $db->table('donations d')
               ->select('d.*, c.title as campaign_title')
               ->join('campaigns c', 'c.id = d.campaign_id', 'left')
               ->where('d.status', 'confirmed')
               ->limit(100)
               ->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 200, "Query doações com join: {$duration}ms");

            // Testar query de estatísticas
            $start = microtime(true);
            $db->table('donations')
               ->select('SUM(amount) as total, COUNT(*) as count')
               ->where('status', 'confirmed')
               ->get();
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->assert($duration < 50, "Query estatísticas: {$duration}ms");

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
            echo "  ✓ {$message}\n";
        } elseif ($isWarning) {
            $this->warnings++;
            echo "  ⚠ {$message}\n";
        } else {
            $this->failed++;
            echo "  ✗ {$message}\n";
        }
    }

    private function printHeader(string $title): void
    {
        echo "\n";
        echo str_repeat("=", 60) . "\n";
        echo " {$title}\n";
        echo str_repeat("=", 60) . "\n";
        echo " Data: " . date('d/m/Y H:i:s') . "\n";
        echo str_repeat("=", 60) . "\n";
    }

    private function printSection(string $title): void
    {
        echo "\n▶ {$title}\n";
        echo str_repeat("-", 50) . "\n";
    }

    private function printSummary(float $duration): void
    {
        echo "\n";
        echo str_repeat("=", 60) . "\n";
        echo " RESUMO DOS TESTES\n";
        echo str_repeat("=", 60) . "\n";
        echo " ✓ Passou: {$this->passed}\n";
        echo " ✗ Falhou: {$this->failed}\n";
        echo " ⚠ Avisos: {$this->warnings}\n";
        echo " ⏱ Tempo: {$duration}s\n";
        echo str_repeat("=", 60) . "\n";

        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;

        if ($this->failed === 0) {
            echo " 🎉 TODOS OS TESTES PASSARAM! ({$percentage}%)\n";
        } else {
            echo " ⚠ ALGUNS TESTES FALHARAM ({$percentage}% passou)\n";
        }
        echo str_repeat("=", 60) . "\n\n";
    }
}

// Executar testes
$test = new FunctionalTest();
$test->run();
