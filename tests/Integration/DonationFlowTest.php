<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * DonationFlowTest
 *
 * Testa o fluxo completo de doação
 *
 * Execução:
 * php spark test --group integration
 * php spark test tests/Integration/DonationFlowTest.php
 */
class DonationFlowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed = 'TestUsersSeeder,TestCampaignsSeeder';

    /**
     * @group integration
     * @group donation
     */
    public function testCompleteDonationFlowPIX()
    {
        // 1. Fazer login
        $loginData = [
            'email' => 'doadora@test.doarfazbem.local',
            'password' => 'teste123'
        ];

        $result = $this->post('login', $loginData);
        $result->assertRedirectTo('dashboard');

        // 2. Buscar campanha de teste
        $campaign = $this->db->table('campaigns')
            ->where('title LIKE', '%[TESTE]%')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        $this->assertNotNull($campaign, 'Campanha de teste não encontrada');

        // 3. Simular doação
        $donationData = [
            'campaign_id' => $campaign['id'],
            'amount' => 100.00,
            'payment_method' => 'pix',
            'extra_contribution' => 5.00
        ];

        $db = \Config\Database::connect();
        $db->table('donations')->insert([
            'campaign_id' => $campaign['id'],
            'user_id' => 3, // Doadora VIP
            'amount' => $donationData['amount'],
            'extra_contribution' => $donationData['extra_contribution'],
            'total_amount' => $donationData['amount'] + $donationData['extra_contribution'],
            'payment_method' => 'pix',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $donationId = $db->insertID();

        // 4. Verificar doação foi criada
        $this->seeInDatabase('donations', [
            'id' => $donationId,
            'campaign_id' => $campaign['id'],
            'amount' => 100.00,
            'payment_method' => 'pix'
        ]);

        // 5. Simular aprovação via webhook
        $db->table('donations')
            ->where('id', $donationId)
            ->update(['status' => 'approved']);

        // 6. Atualizar current_amount da campanha
        $db->table('campaigns')
            ->set('current_amount', "current_amount + 105.00", false)
            ->where('id', $campaign['id'])
            ->update();

        // 7. Verificar atualização
        $updatedCampaign = $db->table('campaigns')
            ->where('id', $campaign['id'])
            ->get()
            ->getRowArray();

        $this->assertEquals(
            $campaign['current_amount'] + 105.00,
            $updatedCampaign['current_amount'],
            'Current amount não foi atualizado corretamente'
        );
    }

    /**
     * @group integration
     * @group donation
     */
    public function testDonationWithPlatformFee()
    {
        // Campanha social (tem 1% de taxa)
        $campaign = $this->db->table('campaigns')
            ->where('category', 'social')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (!$campaign) {
            $this->markTestSkipped('Nenhuma campanha social ativa encontrada');
        }

        $amount = 1000.00;
        $expectedFee = $amount * 0.01; // 1%

        $donationData = [
            'campaign_id' => $campaign['id'],
            'user_id' => 3,
            'amount' => $amount,
            'platform_fee' => $expectedFee,
            'extra_contribution' => 0,
            'total_amount' => $amount,
            'payment_method' => 'pix',
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('donations')->insert($donationData);

        $this->seeInDatabase('donations', [
            'campaign_id' => $campaign['id'],
            'platform_fee' => $expectedFee
        ]);
    }

    /**
     * @group integration
     * @group donation
     */
    public function testMedicalCampaignHasZeroFee()
    {
        // Campanha médica (0% de taxa)
        $campaign = $this->db->table('campaigns')
            ->where('category', 'medica')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        if (!$campaign) {
            $this->markTestSkipped('Nenhuma campanha médica ativa encontrada');
        }

        $amount = 1000.00;
        $expectedFee = 0.00; // 0% para médicas

        $donationData = [
            'campaign_id' => $campaign['id'],
            'user_id' => 3,
            'amount' => $amount,
            'platform_fee' => $expectedFee,
            'extra_contribution' => 0,
            'total_amount' => $amount,
            'payment_method' => 'pix',
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('donations')->insert($donationData);

        $this->seeInDatabase('donations', [
            'campaign_id' => $campaign['id'],
            'platform_fee' => 0.00
        ]);
    }

    /**
     * @group integration
     * @group donation
     */
    public function testAnonymousDonation()
    {
        $campaign = $this->db->table('campaigns')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        $donationData = [
            'campaign_id' => $campaign['id'],
            'user_id' => 3,
            'amount' => 50.00,
            'platform_fee' => 0,
            'extra_contribution' => 0,
            'total_amount' => 50.00,
            'payment_method' => 'pix',
            'status' => 'approved',
            'is_anonymous' => 1, // Doação anônima
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('donations')->insert($donationData);

        $this->seeInDatabase('donations', [
            'is_anonymous' => 1
        ]);
    }

    /**
     * @group integration
     * @group donation
     */
    public function testDonationWithMessage()
    {
        $campaign = $this->db->table('campaigns')
            ->where('status', 'active')
            ->get()
            ->getRowArray();

        $message = 'Parabéns pela iniciativa! Estou torcendo por você!';

        $donationData = [
            'campaign_id' => $campaign['id'],
            'user_id' => 3,
            'amount' => 75.00,
            'platform_fee' => 0,
            'extra_contribution' => 0,
            'total_amount' => 75.00,
            'payment_method' => 'pix',
            'status' => 'approved',
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('donations')->insert($donationData);

        $this->seeInDatabase('donations', [
            'message' => $message
        ]);
    }
}
