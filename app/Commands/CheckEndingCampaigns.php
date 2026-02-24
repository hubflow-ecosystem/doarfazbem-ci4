<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CampaignModel;
use App\Services\NotificationService;

/**
 * Comando para verificar campanhas que estão perto de acabar
 * Executar diariamente via cron/Task Scheduler
 */
class CheckEndingCampaigns extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'campaigns:check-ending';
    protected $description = 'Verifica campanhas que terminam em 7 dias e notifica doadores';
    protected $usage       = 'campaigns:check-ending';

    public function run(array $params)
    {
        CLI::write('Verificando campanhas que terminam em breve...', 'yellow');

        $campaignModel = new CampaignModel();
        $notificationService = new NotificationService();

        try {
            // Buscar campanhas que terminam entre hoje e daqui 7 dias
            $today = date('Y-m-d');
            $sevenDaysFromNow = date('Y-m-d', strtotime('+7 days'));

            $campaigns = $campaignModel
                ->where('status', 'active')
                ->where('end_date >=', $today)
                ->where('end_date <=', $sevenDaysFromNow)
                ->findAll();

            if (empty($campaigns)) {
                CLI::write('✓ Nenhuma campanha terminando nos próximos 7 dias.', 'green');
                return;
            }

            CLI::write('Encontradas ' . count($campaigns) . ' campanha(s) terminando em breve:', 'cyan');

            $notified = 0;
            $errors = 0;

            foreach ($campaigns as $campaign) {
                $daysRemaining = (strtotime($campaign['end_date']) - time()) / 86400;
                $daysRemaining = round($daysRemaining);

                CLI::write("  - ID {$campaign['id']}: {$campaign['title']} (termina em {$daysRemaining} dias)", 'white');

                // Notificar doadores
                $result = $notificationService->notifyDonorsCampaignEndingSoon($campaign['id']);

                if ($result) {
                    $notified++;
                    CLI::write("    ✓ Doadores notificados", 'green');
                } else {
                    $errors++;
                    CLI::write("    ✗ Erro ao notificar doadores", 'red');
                }
            }

            CLI::newLine();
            CLI::write("Resumo:", 'yellow');
            CLI::write("  Campanhas processadas: " . count($campaigns), 'white');
            CLI::write("  Notificações enviadas: {$notified}", 'green');

            if ($errors > 0) {
                CLI::write("  Erros: {$errors}", 'red');
            }

            CLI::newLine();
            CLI::write('✓ Verificação concluída!', 'green');

        } catch (\Exception $e) {
            CLI::error('Erro ao verificar campanhas: ' . $e->getMessage());
            log_message('error', 'CheckEndingCampaigns - ' . $e->getMessage());
        }
    }
}
