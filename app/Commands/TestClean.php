<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comando Test:Clean
 *
 * Remove todos os dados de teste do banco de dados
 *
 * Uso:
 * php spark test:clean
 * php spark test:clean --force
 */
class TestClean extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:clean';
    protected $description = 'Remove todos os dados de teste do banco de dados';
    protected $usage       = 'test:clean [options]';
    protected $arguments   = [];
    protected $options     = [
        '--force' => 'Remove sem confirmação',
        '--users-only' => 'Remove apenas usuários de teste',
        '--campaigns-only' => 'Remove apenas campanhas de teste',
        '--donations-only' => 'Remove apenas doações de teste'
    ];

    public function run(array $params)
    {
        $force = array_key_exists('force', $params);
        $usersOnly = array_key_exists('users-only', $params);
        $campaignsOnly = array_key_exists('campaigns-only', $params);
        $donationsOnly = array_key_exists('donations-only', $params);

        CLI::newLine();
        CLI::write('╔════════════════════════════════════════════════════════════╗', 'red');
        CLI::write('║                                                            ║', 'red');
        CLI::write('║           🗑️  LIMPAR DADOS DE TESTE  🗑️                    ║', 'red');
        CLI::write('║                                                            ║', 'red');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'red');
        CLI::newLine();

        $db = \Config\Database::connect();

        // Contar registros antes
        $usersCount = $db->table('users')->where('email LIKE', '%@test.doarfazbem.local')->countAllResults();
        $campaignsCount = $db->table('campaigns')->where('title LIKE', '%[TESTE]%')->countAllResults();
        $donationsCount = $db->table('donations')
            ->join('campaigns', 'campaigns.id = donations.campaign_id')
            ->where('campaigns.title LIKE', '%[TESTE]%')
            ->countAllResults();

        CLI::write('📊 DADOS A SEREM REMOVIDOS:', 'yellow');
        CLI::newLine();

        if (!$campaignsOnly && !$donationsOnly) {
            CLI::write("   👥 Usuários de teste: {$usersCount}", 'cyan');
        }

        if (!$usersOnly && !$donationsOnly) {
            CLI::write("   🎯 Campanhas de teste: {$campaignsCount}", 'cyan');
        }

        if (!$usersOnly && !$campaignsOnly) {
            CLI::write("   💰 Doações de teste: {$donationsCount}", 'cyan');
        }

        CLI::newLine();

        // Confirmar antes de deletar
        if (!$force) {
            $confirm = CLI::prompt('Tem certeza que deseja remover esses dados?', ['s', 'n']);

            if (strtolower($confirm) !== 's') {
                CLI::write('❌ Operação cancelada.', 'yellow');
                CLI::newLine();
                return;
            }
        }

        CLI::newLine();
        CLI::write('🗑️  Removendo dados de teste...', 'yellow');
        CLI::newLine();

        $removed = 0;

        try {
            // Remover doações de teste
            if (!$usersOnly && !$campaignsOnly) {
                $deletedDonations = $db->query("
                    DELETE donations FROM donations
                    INNER JOIN campaigns ON campaigns.id = donations.campaign_id
                    WHERE campaigns.title LIKE '%[TESTE]%'
                ")->affectedRows();

                CLI::write("✅ {$deletedDonations} doações removidas", 'green');
                $removed += $deletedDonations;
            }

            // Remover campanhas de teste
            if (!$usersOnly && !$donationsOnly) {
                $deletedCampaigns = $db->table('campaigns')
                    ->where('title LIKE', '%[TESTE]%')
                    ->delete();

                CLI::write("✅ {$deletedCampaigns} campanhas removidas", 'green');
                $removed += $deletedCampaigns;
            }

            // Remover usuários de teste
            if (!$campaignsOnly && !$donationsOnly) {
                $deletedUsers = $db->table('users')
                    ->where('email LIKE', '%@test.doarfazbem.local')
                    ->delete();

                CLI::write("✅ {$deletedUsers} usuários removidos", 'green');
                $removed += $deletedUsers;
            }

            CLI::newLine();
            CLI::write("✨ Total removido: {$removed} registros", 'green');
            CLI::newLine();

            CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
            CLI::write('║                    ✅ CONCLUÍDO ✅                         ║', 'green');
            CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
            CLI::newLine();

        } catch (\Exception $e) {
            CLI::write('❌ Erro ao remover dados: ' . $e->getMessage(), 'red');
            CLI::newLine();
        }
    }
}
