<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * CreateCampaignTest
 *
 * Testa a funcionalidade completa de criação de campanhas
 *
 * Execução:
 * php spark test --group feature
 * php spark test tests/Feature/CreateCampaignTest.php
 */
class CreateCampaignTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed = 'TestUsersSeeder';

    /**
     * @group feature
     * @group campaign
     */
    public function testUserCanCreateMedicalCampaign()
    {
        $campaignData = [
            'user_id' => 2, // Criador de campanhas
            'title' => 'Nova Campanha Médica de Teste',
            'slug' => 'nova-campanha-medica-de-teste',
            'description' => 'Descrição detalhada da campanha médica para testes.',
            'category' => 'medica',
            'goal_amount' => 25000.00,
            'current_amount' => 0.00,
            'end_date' => date('Y-m-d', strtotime('+60 days')),
            'status' => 'pending', // Aguardando aprovação
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaignData);
        $campaignId = $this->db->insertID();

        $this->seeInDatabase('campaigns', [
            'id' => $campaignId,
            'category' => 'medica',
            'status' => 'pending'
        ]);
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testCampaignSlugIsUnique()
    {
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);

        $slug = 'campanha-teste-unica';

        // Primeira campanha
        $campaign1 = [
            'user_id' => 2,
            'title' => 'Campanha 1',
            'slug' => $slug,
            'description' => 'Descrição 1',
            'category' => 'social',
            'goal_amount' => 10000,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Segunda campanha com mesmo slug
        $campaign2 = [
            'user_id' => 2,
            'title' => 'Campanha 2',
            'slug' => $slug, // DUPLICADO
            'description' => 'Descrição 2',
            'category' => 'educacao',
            'goal_amount' => 5000,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaign1);
        $this->db->table('campaigns')->insert($campaign2); // Deve falhar
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testCampaignStartsAsPending()
    {
        $campaignData = [
            'user_id' => 2,
            'title' => 'Teste Status Inicial',
            'slug' => 'teste-status-inicial',
            'description' => 'Descrição',
            'category' => 'social',
            'goal_amount' => 5000,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaignData);
        $campaignId = $this->db->insertID();

        $campaign = $this->db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();

        $this->assertEquals('pending', $campaign['status']);
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testCanApproveCampaign()
    {
        // Criar campanha pendente
        $campaignData = [
            'user_id' => 2,
            'title' => 'Teste Aprovação',
            'slug' => 'teste-aprovacao',
            'description' => 'Descrição',
            'category' => 'medica',
            'goal_amount' => 10000,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaignData);
        $campaignId = $this->db->insertID();

        // Aprovar campanha (ação do admin)
        $this->db->table('campaigns')
            ->where('id', $campaignId)
            ->update(['status' => 'active']);

        $this->seeInDatabase('campaigns', [
            'id' => $campaignId,
            'status' => 'active'
        ]);
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testCanRejectCampaign()
    {
        $campaignData = [
            'user_id' => 2,
            'title' => 'Teste Rejeição',
            'slug' => 'teste-rejeicao',
            'description' => 'Descrição',
            'category' => 'social',
            'goal_amount' => 5000,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaignData);
        $campaignId = $this->db->insertID();

        // Rejeitar campanha
        $this->db->table('campaigns')
            ->where('id', $campaignId)
            ->update(['status' => 'rejected']);

        $this->seeInDatabase('campaigns', [
            'id' => $campaignId,
            'status' => 'rejected'
        ]);
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testCampaignReachesGoal()
    {
        // Criar campanha
        $goalAmount = 10000.00;

        $campaignData = [
            'user_id' => 2,
            'title' => 'Teste Meta Atingida',
            'slug' => 'teste-meta-atingida',
            'description' => 'Descrição',
            'category' => 'medica',
            'goal_amount' => $goalAmount,
            'current_amount' => 0,
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('campaigns')->insert($campaignData);
        $campaignId = $this->db->insertID();

        // Simular doações até atingir meta
        $this->db->table('campaigns')
            ->where('id', $campaignId)
            ->update(['current_amount' => $goalAmount]);

        $campaign = $this->db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();

        $this->assertEquals($goalAmount, $campaign['current_amount']);
        $this->assertGreaterThanOrEqual($campaign['goal_amount'], $campaign['current_amount']);

        // Marcar como completa
        $this->db->table('campaigns')
            ->where('id', $campaignId)
            ->update(['status' => 'completed']);

        $this->seeInDatabase('campaigns', [
            'id' => $campaignId,
            'status' => 'completed'
        ]);
    }

    /**
     * @group feature
     * @group campaign
     */
    public function testDifferentCampaignCategories()
    {
        $categories = ['medica', 'social', 'educacao', 'negocio', 'criativa'];

        foreach ($categories as $category) {
            $campaignData = [
                'user_id' => 2,
                'title' => "Teste Categoria {$category}",
                'slug' => "teste-categoria-{$category}",
                'description' => "Campanha de teste para categoria {$category}",
                'category' => $category,
                'goal_amount' => 5000,
                'end_date' => date('Y-m-d', strtotime('+30 days')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('campaigns')->insert($campaignData);

            $this->seeInDatabase('campaigns', [
                'category' => $category,
                'slug' => "teste-categoria-{$category}"
            ]);
        }
    }
}
