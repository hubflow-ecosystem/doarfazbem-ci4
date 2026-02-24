<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comando Test
 *
 * Executa testes PHPUnit
 */
class Test extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test';
    protected $description = 'Executa testes PHPUnit';
    protected $usage       = 'test [options]';
    protected $arguments   = [];
    protected $options     = [
        '--group' => 'Executa apenas testes de um grupo específico (unit, integration, feature)'
    ];

    public function run(array $params)
    {
        $group = $params['group'] ?? null;

        $command = 'vendor/bin/phpunit --colors=always';

        if ($group) {
            $command .= ' --group ' . $group;
        }

        CLI::write('Executando testes PHPUnit...', 'cyan');
        CLI::newLine();

        // Executar PHPUnit
        passthru($command, $returnCode);

        return $returnCode;
    }
}
