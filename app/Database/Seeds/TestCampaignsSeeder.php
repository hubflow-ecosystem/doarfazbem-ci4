<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

/**
 * TestCampaignsSeeder
 *
 * Cria campanhas de teste variadas
 *
 * Uso:
 * php spark db:seed TestCampaignsSeeder
 *
 * IMPORTANTE: Execute TestUsersSeeder primeiro!
 */
class TestCampaignsSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('pt_BR');

        // Limpar campanhas de teste anteriores
        $this->db->query("DELETE FROM campaigns WHERE title LIKE '%[TESTE]%'");

        echo "\n🧪 Criando campanhas de teste...\n\n";

        // Buscar IDs de usuários de teste
        $users = $this->db->table('users')
            ->where('email LIKE', '%@test.doarfazbem.local')
            ->get()
            ->getResultArray();

        if (empty($users)) {
            echo "❌ ERRO: Nenhum usuário de teste encontrado!\n";
            echo "   Execute primeiro: php spark db:seed TestUsersSeeder\n\n";
            return;
        }

        $userIds = array_column($users, 'id');

        // Templates de campanhas
        $campaignTemplates = [
            // Campanhas Médicas (0% taxa)
            [
                'category' => 'medica',
                'title' => '[TESTE] Tratamento de Câncer - Maria Silva',
                'description' => 'Campanha de teste para tratamento oncológico. Maria precisa de ajuda para custear quimioterapia e medicamentos.',
                'goal_amount' => 50000.00,
                'current_amount' => 15000.00,
                'status' => 'active'
            ],
            [
                'category' => 'medica',
                'title' => '[TESTE] Cirurgia Cardíaca Urgente',
                'description' => 'João necessita de cirurgia cardíaca urgente. Família sem condições de arcar com os custos.',
                'goal_amount' => 80000.00,
                'current_amount' => 42000.00,
                'status' => 'active'
            ],
            [
                'category' => 'medica',
                'title' => '[TESTE] Fisioterapia Pós-AVC',
                'description' => 'Campanha para custear sessões de fisioterapia após AVC. Tratamento de longa duração.',
                'goal_amount' => 25000.00,
                'current_amount' => 25000.00,
                'status' => 'completed'
            ],

            // Campanhas Sociais (1% taxa)
            [
                'category' => 'social',
                'title' => '[TESTE] Reforma de Creche Comunitária',
                'description' => 'Arrecadação para reforma de creche que atende 150 crianças em comunidade carente.',
                'goal_amount' => 30000.00,
                'current_amount' => 18500.00,
                'status' => 'active'
            ],
            [
                'category' => 'social',
                'title' => '[TESTE] Doação de Cestas Básicas',
                'description' => 'Campanha mensal para distribuição de cestas básicas a 50 famílias necessitadas.',
                'goal_amount' => 15000.00,
                'current_amount' => 8200.00,
                'status' => 'active'
            ],
            [
                'category' => 'educacao',
                'title' => '[TESTE] Projeto Educação Digital',
                'description' => 'Compra de computadores e tablets para escola pública. Inclusão digital para 200 alunos.',
                'goal_amount' => 45000.00,
                'current_amount' => 12000.00,
                'status' => 'active'
            ],

            // Campanhas de Negócio
            [
                'category' => 'negocio',
                'title' => '[TESTE] Pequena Empresa Familiar',
                'description' => 'Família perdeu tudo em incêndio. Necessita de móveis, roupas e utensílios básicos.',
                'goal_amount' => 20000.00,
                'current_amount' => 5400.00,
                'status' => 'active'
            ],

            // Campanhas Pendentes/Rejeitadas (para testes)
            [
                'category' => 'medica',
                'title' => '[TESTE] Campanha Pendente - Teste',
                'description' => 'Esta campanha está pendente de aprovação para testes.',
                'goal_amount' => 10000.00,
                'current_amount' => 0.00,
                'status' => 'pending'
            ],
            [
                'category' => 'social',
                'title' => '[TESTE] Campanha Rejeitada - Teste',
                'description' => 'Esta campanha foi rejeitada para testes de status.',
                'goal_amount' => 5000.00,
                'current_amount' => 0.00,
                'status' => 'rejected'
            ],

            // Campanha recém-criada (sem doações)
            [
                'category' => 'medica',
                'title' => '[TESTE] Nova Campanha - Sem Doações',
                'description' => 'Campanha recém-criada para testar estado inicial sem doações.',
                'goal_amount' => 35000.00,
                'current_amount' => 0.00,
                'status' => 'active'
            ]
        ];

        $createdCount = 0;

        foreach ($campaignTemplates as $template) {
            // Selecionar usuário aleatório como criador
            $userId = $userIds[array_rand($userIds)];

            $campaignData = [
                'user_id' => $userId,
                'category' => $template['category'],
                'title' => $template['title'],
                'slug' => $this->generateSlug($template['title']),
                'description' => $template['description'],
                'goal_amount' => $template['goal_amount'],
                'current_amount' => $template['current_amount'],
                'status' => $template['status'],
                'end_date' => $faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
                'created_at' => $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('campaigns')->insert($campaignData);
            $createdCount++;

            $statusEmoji = match($template['status']) {
                'active' => '✅',
                'completed' => '🎉',
                'paused' => '⏸️',
                'cancelled' => '❌',
                default => '📋'
            };

            $progress = $template['goal_amount'] > 0
                ? round(($template['current_amount'] / $template['goal_amount']) * 100, 1)
                : 0;

            echo "{$statusEmoji} {$template['title']}\n";
            echo "   Meta: R$ " . number_format($template['goal_amount'], 2, ',', '.') . " | ";
            echo "Arrecadado: R$ " . number_format($template['current_amount'], 2, ',', '.') . " ";
            echo "({$progress}%)\n\n";
        }

        echo "✨ Total: {$createdCount} campanhas de teste criadas!\n\n";
        echo "📊 RESUMO POR CATEGORIA:\n";
        echo "   - Médicas (0% taxa): 4 campanhas\n";
        echo "   - Sociais (1% taxa): 3 campanhas\n";
        echo "   - Emergência: 1 campanha\n";
        echo "   - Testes de Status: 2 campanhas\n\n";
        echo "📈 RESUMO POR STATUS:\n";
        echo "   - Ativas: 7 campanhas\n";
        echo "   - Concluída: 1 campanha\n";
        echo "   - Pausada: 1 campanha\n";
        echo "   - Cancelada: 1 campanha\n\n";
    }

    /**
     * Gera slug a partir do título
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 100);
    }
}
