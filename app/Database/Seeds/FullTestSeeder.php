<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FullTestSeeder
 *
 * Executa TODOS os seeders de teste em sequência
 *
 * Uso:
 * php spark db:seed FullTestSeeder
 *
 * CUIDADO: Este seeder criará muitos dados de teste!
 */
class FullTestSeeder extends Seeder
{
    public function run()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                                                            ║\n";
        echo "║           🧪 DOARFAZBEM - TEST DATA SEEDER 🧪              ║\n";
        echo "║                                                            ║\n";
        echo "║  Este seeder irá criar dados de teste completos:          ║\n";
        echo "║  • Usuários (admin, criadores, doadores)                  ║\n";
        echo "║  • Campanhas (médicas, sociais, emergência)               ║\n";
        echo "║  • Doações (PIX, Boleto, Cartão) - EM BREVE              ║\n";
        echo "║                                                            ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        $startTime = microtime(true);

        // 1. Criar usuários
        echo "┌─────────────────────────────────────────────────────────┐\n";
        echo "│ ETAPA 1/2: Criando Usuários de Teste                   │\n";
        echo "└─────────────────────────────────────────────────────────┘\n";
        $this->call('TestUsersSeeder');

        // 2. Criar campanhas
        echo "\n┌─────────────────────────────────────────────────────────┐\n";
        echo "│ ETAPA 2/2: Criando Campanhas de Teste                  │\n";
        echo "└─────────────────────────────────────────────────────────┘\n";
        $this->call('TestCampaignsSeeder');

        // 3. Criar doações (em breve)
        // echo "\n┌─────────────────────────────────────────────────────────┐\n";
        // echo "│ ETAPA 3/3: Criando Doações de Teste                    │\n";
        // echo "└─────────────────────────────────────────────────────────┘\n";
        // $this->call('TestDonationsSeeder');

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                    ✨ CONCLUÍDO ✨                         ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "⏱️  Tempo de execução: {$executionTime} segundos\n";
        echo "\n";
        echo "📋 DADOS CRIADOS:\n";
        echo "   ✅ 24 usuários de teste\n";
        echo "   ✅ 10 campanhas de teste\n";
        echo "   ⏳ Doações (em breve)\n";
        echo "\n";
        echo "🔑 CREDENCIAIS PADRÃO:\n";
        echo "   Admin: admin@test.doarfazbem.local / admin123\n";
        echo "   Users: user1@test.doarfazbem.local / teste123\n";
        echo "          (user1 até user20)\n";
        echo "\n";
        echo "🌐 ACESSO:\n";
        echo "   https://doarfazbem.ai/login\n";
        echo "\n";
    }
}
