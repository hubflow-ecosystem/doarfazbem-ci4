<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\MercadoPagoService;

/**
 * Comando para testar a integração com Mercado Pago
 *
 * Uso: php spark test:mercadopago
 */
class TestMercadoPago extends BaseCommand
{
    protected $group       = 'Testes';
    protected $name        = 'test:mercadopago';
    protected $description = 'Testa a integração completa com Mercado Pago (Sandbox)';

    public function run(array $params)
    {
        CLI::write('╔════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║  TESTE DE INTEGRAÇÃO MERCADO PAGO - SANDBOX           ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        // Verificar ambiente
        $this->testEnvironment();

        // Testar credenciais
        $this->testCredentials();

        // Testar criação de pagamento PIX
        $paymentId = $this->testCreatePixPayment();

        if ($paymentId) {
            // Testar consulta de status
            $this->testGetPaymentStatus($paymentId);

            // Simular webhook
            $this->testWebhook($paymentId);
        }

        CLI::newLine();
        CLI::write('╔════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║  TESTE CONCLUÍDO                                      ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════╝', 'green');
    }

    protected function testEnvironment()
    {
        CLI::write('📋 1. Verificando Ambiente...', 'yellow');

        $config = config('MercadoPago');

        if ($config->isProduction()) {
            CLI::error('   ❌ ATENÇÃO: Sistema está em PRODUÇÃO!');
            CLI::write('   Mude para sandbox antes de testar.', 'red');
            exit(1);
        }

        CLI::write('   ✅ Ambiente: SANDBOX (Testes)', 'green');
        CLI::newLine();
    }

    protected function testCredentials()
    {
        CLI::write('🔑 2. Verificando Credenciais...', 'yellow');

        $config = config('MercadoPago');

        if (!$config->isConfigured()) {
            CLI::error('   ❌ Credenciais não configuradas!');
            CLI::write('   Configure as credenciais no arquivo .env', 'red');
            exit(1);
        }

        $publicKey = $config->getPublicKey();
        $accessToken = $config->getAccessToken();

        CLI::write('   ✅ Public Key: ' . substr($publicKey, 0, 20) . '...', 'green');
        CLI::write('   ✅ Access Token: ' . substr($accessToken, 0, 20) . '...', 'green');
        CLI::newLine();
    }

    protected function testCreatePixPayment()
    {
        CLI::write('💳 3. Criando Pagamento PIX de Teste...', 'yellow');

        $mercadoPago = new MercadoPagoService();

        $testData = [
            'amount' => 50.00,
            'description' => 'Teste de Integração - 5 Números da Sorte',
            'email' => 'test' . time() . '@test.com',
            'name' => 'Usuario Teste DoarFazBem',
            'cpf' => '12345678909', // CPF de teste
            'external_reference' => 'TEST_' . time(),
        ];

        CLI::write('   Dados do pagamento:', 'cyan');
        CLI::write('   - Valor: R$ ' . number_format($testData['amount'], 2, ',', '.'), 'white');
        CLI::write('   - Email: ' . $testData['email'], 'white');
        CLI::write('   - CPF: ' . $testData['cpf'], 'white');
        CLI::newLine();

        $result = $mercadoPago->createPixPayment($testData);

        if (!$result['success']) {
            CLI::error('   ❌ Erro ao criar pagamento: ' . ($result['error'] ?? 'Erro desconhecido'));
            return null;
        }

        CLI::write('   ✅ Pagamento criado com sucesso!', 'green');
        CLI::write('   Payment ID: ' . $result['payment_id'], 'green');
        CLI::write('   Status: ' . $result['status'], 'yellow');

        if (!empty($result['pix_code'])) {
            CLI::write('   PIX Copia e Cola: ' . substr($result['pix_code'], 0, 50) . '...', 'cyan');
        }

        if (!empty($result['development_mode'])) {
            CLI::write('   ⚠️  MODO DESENVOLVIMENTO (sem credenciais reais)', 'yellow');
        }

        CLI::newLine();
        return $result['payment_id'];
    }

    protected function testGetPaymentStatus($paymentId)
    {
        CLI::write('🔍 4. Consultando Status do Pagamento...', 'yellow');

        $mercadoPago = new MercadoPagoService();

        $result = $mercadoPago->getPaymentStatus($paymentId);

        if (!$result['success']) {
            CLI::error('   ❌ Erro ao consultar: ' . ($result['error'] ?? 'Erro desconhecido'));
            return;
        }

        CLI::write('   ✅ Consulta realizada com sucesso!', 'green');
        CLI::write('   Status: ' . $result['status'], 'yellow');

        if (!empty($result['status_detail'])) {
            CLI::write('   Detalhe: ' . $result['status_detail'], 'cyan');
        }

        if (!empty($result['date_approved'])) {
            CLI::write('   Data Aprovação: ' . $result['date_approved'], 'green');
        }

        CLI::newLine();
    }

    protected function testWebhook($paymentId)
    {
        CLI::write('🔔 5. Simulando Webhook de Pagamento Aprovado...', 'yellow');

        $webhookData = [
            'action' => 'payment.updated',
            'type' => 'payment',
            'data' => [
                'id' => $paymentId
            ],
        ];

        CLI::write('   Dados do webhook:', 'cyan');
        CLI::write('   ' . json_encode($webhookData, JSON_PRETTY_PRINT), 'white');
        CLI::newLine();

        $mercadoPago = new MercadoPagoService();
        $result = $mercadoPago->processWebhook($webhookData);

        if (!$result['success']) {
            CLI::error('   ❌ Erro ao processar webhook: ' . ($result['error'] ?? 'Erro desconhecido'));
            return;
        }

        CLI::write('   ✅ Webhook processado com sucesso!', 'green');
        CLI::write('   Payment ID: ' . $result['payment_id'], 'green');
        CLI::write('   Status: ' . $result['status'], 'yellow');
        CLI::write('   Aprovado: ' . ($result['is_approved'] ? 'SIM' : 'NÃO'), $result['is_approved'] ? 'green' : 'red');
        CLI::newLine();
    }
}
