<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comando Test:Full
 *
 * Executa suite completa de testes do DoarFazBem
 *
 * Uso:
 * php spark test:full
 */
class TestFull extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:full';
    protected $description = 'Executa suite completa de testes (seeders + scripts + PHPUnit)';
    protected $usage       = 'test:full [options]';
    protected $arguments   = [];
    protected $options     = [
        '--skip-seed' => 'Pula criação de dados de teste (seeders)',
        '--skip-tests' => 'Pula testes automatizados',
        '--verbose' => 'Modo verbose com mais detalhes'
    ];

    public function run(array $params)
    {
        $verbose = array_key_exists('verbose', $params);
        $skipSeed = array_key_exists('skip-seed', $params);
        // Testes habilitados por padrão
        $skipPhpUnit = array_key_exists('skip-tests', $params);

        CLI::newLine();
        CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║                                                            ║', 'green');
        CLI::write('║           🧪 DOARFAZBEM - FULL TEST SUITE 🧪               ║', 'green');
        CLI::write('║                                                            ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        $startTime = microtime(true);
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;

        // ETAPA 1: Seeders
        if (!$skipSeed) {
            $totalSteps = $skipPhpUnit ? 3 : 4;
            CLI::write('┌─────────────────────────────────────────────────────────┐', 'yellow');
            CLI::write("│ ETAPA 1/{$totalSteps}: Criando Dados de Teste (Seeders)            │", 'yellow');
            CLI::write('└─────────────────────────────────────────────────────────┘', 'yellow');
            CLI::newLine();

            try {
                $output = [];
                $rootPath = ROOTPATH;
                exec("cd {$rootPath} && php spark db:seed FullTestSeeder 2>&1", $output, $returnCode);

                if ($returnCode === 0) {
                    CLI::write('✅ Seeders executados com sucesso!', 'green');
                    $passedTests++;
                } else {
                    CLI::write('❌ Erro ao executar seeders', 'red');
                    $failedTests++;

                    if ($verbose) {
                        foreach ($output as $line) {
                            CLI::write('   ' . $line, 'red');
                        }
                    }
                }

                $totalTests++;
            } catch (\Exception $e) {
                CLI::write('❌ Erro: ' . $e->getMessage(), 'red');
                $failedTests++;
            }

            CLI::newLine();
        }

        // ETAPA 2: Script de Doações
        if (!$skipSeed) {
            $totalSteps = $skipPhpUnit ? 3 : 4;
            CLI::write('┌─────────────────────────────────────────────────────────┐', 'yellow');
            CLI::write("│ ETAPA 2/{$totalSteps}: Simulando Doações (Script)                  │", 'yellow');
            CLI::write('└─────────────────────────────────────────────────────────┘', 'yellow');
            CLI::newLine();

            try {
                $output = [];
                $rootPath = ROOTPATH;
                exec("cd {$rootPath} && php tests/scripts/simulate-donations.php 2>&1", $output, $returnCode);

                if ($returnCode === 0) {
                    CLI::write('✅ Doações simuladas com sucesso!', 'green');
                    $passedTests++;
                } else {
                    CLI::write('❌ Erro ao simular doações', 'red');
                    $failedTests++;

                    if ($verbose) {
                        foreach ($output as $line) {
                            CLI::write('   ' . $line, 'red');
                        }
                    }
                }

                $totalTests++;
            } catch (\Exception $e) {
                CLI::write('❌ Erro: ' . $e->getMessage(), 'red');
                $failedTests++;
            }

            CLI::newLine();
        }

        // ETAPA 3: Testes Automatizados
        if (!$skipPhpUnit) {
            $totalSteps = 4;
            CLI::write('┌─────────────────────────────────────────────────────────┐', 'yellow');
            CLI::write("│ ETAPA 3/{$totalSteps}: Testes Automatizados                        │", 'yellow');
            CLI::write('└─────────────────────────────────────────────────────────┘', 'yellow');
            CLI::newLine();

            try {
                $rootPath = ROOTPATH;
                $output = [];
                exec("cd {$rootPath} && php tests/run-simple-tests.php 2>&1", $output, $returnCode);

                if ($returnCode === 0) {
                    CLI::write('✅ Testes automatizados: PASSOU', 'green');
                    $passedTests++;
                } else {
                    CLI::write('❌ Testes automatizados: FALHOU', 'red');
                    $failedTests++;

                    if ($verbose) {
                        foreach ($output as $line) {
                            CLI::write('   ' . $line, 'red');
                        }
                    }
                }

                $totalTests++;
            } catch (\Exception $e) {
                CLI::write('❌ Erro ao executar testes: ' . $e->getMessage(), 'red');
                $failedTests++;
            }

            CLI::newLine();
        }

        // ETAPA 4: Relatório Final
        $totalSteps = $skipPhpUnit ? 3 : 4;
        CLI::write('┌─────────────────────────────────────────────────────────┐', 'yellow');
        CLI::write("│ ETAPA {$totalSteps}/{$totalSteps}: Verificações do Sistema                     │", 'yellow');
        CLI::write('└─────────────────────────────────────────────────────────┘', 'yellow');
        CLI::newLine();

        // Contar registros no banco
        $db = \Config\Database::connect();

        $usersCount = $db->table('users')->where('email LIKE', '%@test.doarfazbem.local')->countAllResults();
        $campaignsCount = $db->table('campaigns')->where('title LIKE', '%[TESTE]%')->countAllResults();
        $donationsCount = $db->table('donations')->countAllResults();

        CLI::write("📊 Usuários de teste: {$usersCount}", 'cyan');
        CLI::write("📊 Campanhas de teste: {$campaignsCount}", 'cyan');
        CLI::write("📊 Doações simuladas: {$donationsCount}", 'cyan');

        CLI::newLine();

        // Relatório Final
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        CLI::write('╔════════════════════════════════════════════════════════════╗', 'green');
        CLI::write('║                    ✨ RELATÓRIO FINAL ✨                   ║', 'green');
        CLI::write('╚════════════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        CLI::write("⏱️  Tempo total: {$executionTime}s", 'yellow');
        CLI::write("📊 Total de testes: {$totalTests}", 'cyan');
        CLI::write("✅ Testes aprovados: {$passedTests}", 'green');
        CLI::write("❌ Testes falhados: {$failedTests}", $failedTests > 0 ? 'red' : 'green');

        $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
        CLI::write("📈 Taxa de sucesso: {$successRate}%", $successRate >= 80 ? 'green' : 'red');

        CLI::newLine();

        if ($failedTests === 0) {
            CLI::write('🎉 TODOS OS TESTES PASSARAM! Sistema funcionando perfeitamente!', 'green');
        } else {
            CLI::write("⚠️  {$failedTests} teste(s) falharam. Revise os logs acima.", 'yellow');
        }

        CLI::newLine();
    }
}
