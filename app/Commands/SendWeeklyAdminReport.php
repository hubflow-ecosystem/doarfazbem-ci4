<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CampaignModel;
use App\Models\Donation;
use App\Models\AdminNotificationPreferences;
use App\Models\UserModel;
use App\Models\NotificationQueue;

/**
 * Comando para enviar relatório semanal aos administradores
 * Mostra progresso das campanhas em incrementos de 10%
 * Executar semanalmente via cron/Task Scheduler
 */
class SendWeeklyAdminReport extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'admin:weekly-report';
    protected $description = 'Envia relatório semanal para administradores com progresso das campanhas';
    protected $usage       = 'admin:weekly-report';

    public function run(array $params)
    {
        CLI::write('Gerando relatório semanal para administradores...', 'yellow');

        $adminPrefsModel = new AdminNotificationPreferences();
        $userModel = new UserModel();
        $queueModel = new NotificationQueue();

        try {
            // Buscar admins que querem receber relatório semanal
            $admins = $adminPrefsModel
                ->where('notify_weekly_report', 1)
                ->findAll();

            if (empty($admins)) {
                CLI::write('✓ Nenhum admin configurado para receber relatório semanal.', 'green');
                return;
            }

            CLI::write('Enviando relatório para ' . count($admins) . ' admin(s)...', 'cyan');

            // Gerar dados do relatório
            $reportData = $this->generateReportData();

            $sent = 0;
            $errors = 0;

            foreach ($admins as $admin) {
                $adminUser = $userModel->find($admin['admin_user_id']);

                if (!$adminUser) {
                    CLI::write("  ✗ Admin ID {$admin['admin_user_id']} não encontrado", 'red');
                    $errors++;
                    continue;
                }

                // Enfileirar email
                $queueModel->insert([
                    'type' => 'weekly_admin_report_email',
                    'recipient_email' => $adminUser['email'],
                    'recipient_name' => $adminUser['name'],
                    'data' => json_encode($reportData),
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $sent++;
                CLI::write("  ✓ Relatório enfileirado para: {$adminUser['name']} ({$adminUser['email']})", 'green');
            }

            CLI::newLine();
            CLI::write("Resumo:", 'yellow');
            CLI::write("  Relatórios enviados: {$sent}", 'green');

            if ($errors > 0) {
                CLI::write("  Erros: {$errors}", 'red');
            }

            CLI::newLine();
            CLI::write('✓ Relatório semanal gerado com sucesso!', 'green');
            CLI::write('Execute "php spark notifications:send" para processar a fila.', 'cyan');

        } catch (\Exception $e) {
            CLI::error('Erro ao gerar relatório: ' . $e->getMessage());
            log_message('error', 'SendWeeklyAdminReport - ' . $e->getMessage());
        }
    }

    /**
     * Gera dados do relatório semanal
     */
    protected function generateReportData(): array
    {
        $campaignModel = new CampaignModel();
        $donationModel = new Donation();

        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        // Estatísticas gerais da semana
        $newDonations = $donationModel
            ->where('created_at >=', $weekAgo)
            ->where('status', 'confirmed')
            ->countAllResults();

        $totalRaisedThisWeek = $donationModel
            ->selectSum('amount')
            ->where('created_at >=', $weekAgo)
            ->where('status', 'confirmed')
            ->first()['amount'] ?? 0;

        $newCampaigns = $campaignModel
            ->where('created_at >=', $weekAgo)
            ->countAllResults();

        // Campanhas ativas com progresso
        $activeCampaigns = $campaignModel
            ->where('status', 'active')
            ->findAll();

        $campaignsProgress = [];
        foreach ($activeCampaigns as $campaign) {
            // Use goal_amount and current_amount instead of goal and raised
            $goal = $campaign['goal_amount'] ?? $campaign['goal'] ?? 0;
            $raised = $campaign['current_amount'] ?? $campaign['raised'] ?? 0;

            $percentage = $goal > 0
                ? round(($raised / $goal) * 100, 1)
                : 0;

            // Encontrar o marco mais próximo (10%, 20%, 30%, etc.)
            $milestone = floor($percentage / 10) * 10;

            $campaignsProgress[] = [
                'id' => $campaign['id'],
                'title' => $campaign['title'],
                'goal' => $goal,
                'raised' => $raised,
                'percentage' => $percentage,
                'milestone' => $milestone,
                'donors_count' => $donationModel
                    ->where('campaign_id', $campaign['id'])
                    ->where('status', 'confirmed')
                    ->countAllResults(),
            ];
        }

        // Ordenar por progresso (maior primeiro)
        usort($campaignsProgress, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        // Campanhas que atingiram metas esta semana
        $completedCampaigns = $campaignModel
            ->where('status', 'completed')
            ->where('updated_at >=', $weekAgo)
            ->findAll();

        return [
            'period_start' => date('d/m/Y', strtotime($weekAgo)),
            'period_end' => date('d/m/Y'),
            'new_donations' => $newDonations,
            'total_raised_week' => $totalRaisedThisWeek,
            'new_campaigns' => $newCampaigns,
            'active_campaigns_count' => count($activeCampaigns),
            'campaigns_progress' => $campaignsProgress,
            'completed_campaigns' => count($completedCampaigns),
            'completed_campaigns_list' => array_map(function($c) {
                return [
                    'title' => $c['title'],
                    'raised' => $c['current_amount'] ?? $c['raised'] ?? 0,
                    'goal' => $c['goal_amount'] ?? $c['goal'] ?? 0,
                ];
            }, $completedCampaigns),
        ];
    }
}
