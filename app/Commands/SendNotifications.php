<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NotificationQueue;
use App\Services\EmailNotificationService;
use App\Services\PushNotificationService;

/**
 * Command para processar fila de notificações
 *
 * Uso:
 * php spark notifications:send
 *
 * Configure no cron para rodar a cada 5 minutos:
 * Every 5 min: cd /path/to/project && php spark notifications:send
 */
class SendNotifications extends BaseCommand
{
    protected $group = 'Notifications';
    protected $name = 'notifications:send';
    protected $description = 'Processa fila de notificações pendentes';

    protected $queueModel;
    protected $emailService;
    protected $pushService;

    public function run(array $params)
    {
        CLI::write('=== Processando Fila de Notificações ===', 'green');
        CLI::newLine();

        $this->queueModel = new NotificationQueue();
        $this->emailService = new EmailNotificationService();
        $this->pushService = new PushNotificationService();

        // Buscar notificações pendentes
        $pending = $this->queueModel->where('status', 'pending')
                                     ->orderBy('created_at', 'ASC')
                                     ->limit(50)
                                     ->findAll();

        if (empty($pending)) {
            CLI::write('✅ Nenhuma notificação pendente', 'yellow');
            return;
        }

        CLI::write(count($pending) . ' notificações pendentes encontradas', 'cyan');
        CLI::newLine();

        $sent = 0;
        $failed = 0;

        foreach ($pending as $notification) {
            try {
                $this->processNotification($notification);

                $this->queueModel->update($notification['id'], [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                ]);
                $sent++;

                CLI::write("✅ [{$notification['id']}] {$notification['type']} enviado para {$notification['recipient_email']}", 'green');

            } catch (\Exception $e) {
                $this->queueModel->update($notification['id'], [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempts' => ($notification['attempts'] ?? 0) + 1,
                ]);
                $failed++;

                CLI::write("❌ [{$notification['id']}] Falha: " . $e->getMessage(), 'red');
            }
        }

        CLI::newLine();
        CLI::write("=== Resumo ===", 'green');
        CLI::write("✅ Enviadas: {$sent}", 'green');
        CLI::write("❌ Falhadas: {$failed}", $failed > 0 ? 'red' : 'green');

        // Limpar notificações antigas (> 30 dias)
        $cleaned = $this->queueModel->where('created_at <', date('Y-m-d H:i:s', strtotime('-30 days')))
                                    ->where('status', 'sent')
                                    ->delete();
        if ($cleaned > 0) {
            CLI::newLine();
            CLI::write("🗑️  {$cleaned} notificações antigas removidas", 'yellow');
        }
    }

    /**
     * Processar notificação baseado no tipo
     */
    protected function processNotification($notification)
    {
        $data = json_decode($notification['data'], true);

        switch ($notification['type']) {
            // ========== NOTIFICAÇÕES DE ATUALIZAÇÃO DE CAMPANHA (ANTIGO) ==========
            case 'campaign_update_email':
                $this->sendCampaignUpdateEmail($notification, $data);
                break;

            case 'campaign_update_push':
                $this->sendCampaignUpdatePush($notification, $data);
                break;

            // ========== NOTIFICAÇÕES DE DOAÇÃO RECEBIDA (NOVO) ==========
            case 'donation_received_email':
                $this->sendDonationReceivedEmail($notification, $data);
                break;

            case 'donation_received_push':
                $this->sendDonationReceivedPush($notification, $data);
                break;

            // ========== NOTIFICAÇÕES DE MARCOS (NOVO) ==========
            case 'campaign_milestone_email':
                $this->sendMilestoneEmail($notification, $data);
                break;

            // ========== NOTIFICAÇÕES PARA DOADORES (NOVO) ==========
            case 'campaign_goal_reached_email':
                $this->sendGoalReachedEmail($notification, $data);
                break;

            case 'campaign_ending_soon_email':
                $this->sendEndingSoonEmail($notification, $data);
                break;

            // ========== NOTIFICAÇÕES ADMIN (NOVO) ==========
            case 'new_campaign_admin_email':
                $this->sendNewCampaignAdminEmail($notification, $data);
                break;

            case 'weekly_admin_report_email':
                $this->sendWeeklyAdminReportEmail($notification, $data);
                break;

            default:
                throw new \Exception("Tipo de notificação desconhecido: {$notification['type']}");
        }
    }

    // ==========================================================================
    // ENVIO DE EMAILS - CAMPANHA UPDATE (ANTIGO - MANTIDO)
    // ==========================================================================

    protected function sendCampaignUpdateEmail($notification, $data)
    {
        $preferenceModel = new \App\Models\NotificationPreference();
        $preference = $preferenceModel->getByDonorAndCampaign(
            $notification['recipient_email'],
            $data['campaign_id']
        );

        if (!$preference || !$preference['notify_email']) {
            throw new \Exception('Doador não quer receber emails');
        }

        $this->emailService->sendCampaignUpdate(
            $data['update_id'],
            $notification['recipient_email'],
            $notification['recipient_name'],
            $preference['unsubscribe_token']
        );
    }

    protected function sendCampaignUpdatePush($notification, $data)
    {
        if (empty($data['push_token'])) {
            throw new \Exception('Push token não fornecido');
        }

        $this->pushService->sendCampaignUpdate(
            $data['update_id'],
            $data['push_token']
        );
    }

    // ==========================================================================
    // ENVIO DE EMAILS - DOAÇÃO RECEBIDA (NOVO)
    // ==========================================================================

    protected function sendDonationReceivedEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject("💰 Nova Doação Recebida - {$data['campaign_title']}");

        $message = $this->buildDonationReceivedTemplate($data, $notification['recipient_name']);
        $email->setMessage($message);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email: ' . $email->printDebugger(['headers']));
        }
    }

    protected function sendDonationReceivedPush($notification, $data)
    {
        if (empty($data['push_token'])) {
            throw new \Exception('Push token não fornecido');
        }

        $this->pushService->send(
            $data['push_token'],
            $data['title'],
            $data['body'],
            ['campaign_id' => $data['campaign_id']]
        );
    }

    // ==========================================================================
    // ENVIO DE EMAILS - MARCOS (NOVO)
    // ==========================================================================

    protected function sendMilestoneEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject("🎯 Marco Atingido: {$data['campaign_title']} - {$data['percentage']}%");

        $message = $this->buildMilestoneTemplate($data, $notification['recipient_name']);
        $email->setMessage($message);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email');
        }
    }

    // ==========================================================================
    // ENVIO DE EMAILS - META ATINGIDA (NOVO)
    // ==========================================================================

    protected function sendGoalReachedEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject("🎉 Meta Atingida! {$data['campaign_title']}");

        $message = $this->buildGoalReachedTemplate($data, $notification['recipient_name']);
        $email->setMessage($message);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email');
        }
    }

    // ==========================================================================
    // ENVIO DE EMAILS - CAMPANHA ACABANDO (NOVO)
    // ==========================================================================

    protected function sendEndingSoonEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject("⏰ Última Chance! {$data['campaign_title']} acaba em {$data['days_remaining']} dias");

        $message = $this->buildEndingSoonTemplate($data, $notification['recipient_name']);
        $email->setMessage($message);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email');
        }
    }

    // ==========================================================================
    // ENVIO DE EMAILS - NOVA CAMPANHA ADMIN (NOVO)
    // ==========================================================================

    protected function sendNewCampaignAdminEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject("📢 Nova Campanha Criada: {$data['campaign_title']}");

        $message = $this->buildNewCampaignAdminTemplate($data, $notification['recipient_name']);
        $email->setMessage($message);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email');
        }
    }

    // ==========================================================================
    // TEMPLATES DE EMAIL
    // ==========================================================================

    protected function buildDonationReceivedTemplate($data, $recipientName)
    {
        $donorName = $data['donor_name'];
        $amount = number_format($data['amount'], 2, ',', '.');
        $message = $data['message'] ?? '';
        $messageHtml = $message !== '' ? "<p style='margin: 10px 0;'><strong>Mensagem:</strong><br><em>\"{$message}\"</em></p>" : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">💰 Nova Doação Recebida!</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>

        <p style="font-size: 16px;">Você acaba de receber uma nova doação em sua campanha <strong>{$data['campaign_title']}</strong>!</p>

        <div style="background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Doador:</strong> {$donorName}</p>
            <p style="margin: 5px 0; font-size: 24px; color: #667eea;"><strong>Valor:</strong> R$ {$amount}</p>
            {$messageHtml}
        </div>

        <p style="font-size: 14px; color: #666;">Continue compartilhando sua campanha para alcançar mais pessoas!</p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="". base_url("campaigns/{$data['campaign_id']}") ."" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Ver Campanha</a>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>DoarFazBem - Transformando vidas através da solidariedade</p>
    </div>
</body>
</html>
HTML;
    }

    protected function buildMilestoneTemplate($data, $recipientName)
    {
        $percentage = $data['percentage'];
        $raised = number_format($data['raised'], 2, ',', '.');
        $goal = number_format($data['goal'], 2, ',', '.');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🎯 Marco Atingido!</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>

        <p style="font-size: 16px;">A campanha <strong>{$data['campaign_title']}</strong> atingiu um novo marco:</p>

        <div style="text-align: center; margin: 30px 0;">
            <div style="font-size: 48px; color: #f5576c; font-weight: bold;">{$percentage}%</div>
            <p style="font-size: 18px; margin: 10px 0;">R$ {$raised} de R$ {$goal}</p>
        </div>

        <p style="font-size: 14px; color: #666;">Parabéns ao criador da campanha! Continue acompanhando o progresso.</p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>DoarFazBem - Administração</p>
    </div>
</body>
</html>
HTML;
    }

    protected function buildGoalReachedTemplate($data, $recipientName)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🎉 Meta Atingida!</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>

        <p style="font-size: 18px; font-weight: bold; color: #11998e;">Temos uma ótima notícia!</p>

        <p style="font-size: 16px;">A campanha <strong>{$data['campaign_title']}</strong> que você apoiou atingiu 100% da meta! 🎊</p>

        <p style="font-size: 14px; color: #666;">Sua contribuição foi fundamental para este sucesso. Obrigado por fazer parte desta conquista!</p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="". base_url("campaigns/{$data['campaign_slug']}") ."" style="background: #11998e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Ver Campanha</a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">
            <a href="". base_url("notifications/unsubscribe/{$data['unsubscribe_token']}") ."" style="color: #999;">Cancelar notificações</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    protected function buildEndingSoonTemplate($data, $recipientName)
    {
        $days = $data['days_remaining'];

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">⏰ Última Chance!</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>

        <p style="font-size: 16px;">A campanha <strong>{$data['campaign_title']}</strong> que você apoiou está chegando ao fim!</p>

        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 18px; color: #856404;"><strong>Faltam apenas {$days} dias!</strong></p>
        </div>

        <p style="font-size: 14px; color: #666;">Se você deseja contribuir novamente ou compartilhar com mais pessoas, esta é a última oportunidade!</p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="". base_url("campaigns/{$data['campaign_slug']}") ."" style="background: #ffc107; color: #000; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">Doar Novamente</a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">
            <a href="". base_url("notifications/unsubscribe/{$data['unsubscribe_token']}") ."" style="color: #999;">Cancelar notificações</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    protected function buildNewCampaignAdminTemplate($data, $recipientName)
    {
        $goal = number_format($data['goal'], 2, ',', '.');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">📢 Nova Campanha Criada</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>

        <p style="font-size: 16px;">Uma nova campanha foi criada na plataforma:</p>

        <div style="background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Campanha:</strong> {$data['campaign_title']}</p>
            <p style="margin: 5px 0;"><strong>Criador:</strong> {$data['creator_name']}</p>
            <p style="margin: 5px 0;"><strong>Meta:</strong> R$ {$goal}</p>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="". base_url("campaigns/{$data['campaign_id']}") ."" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Revisar Campanha</a>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>DoarFazBem - Painel Administrativo</p>
    </div>
</body>
</html>
HTML;
    }

    // ==========================================================================
    // ENVIO DE EMAILS - RELATÓRIO SEMANAL ADMIN (NOVO)
    // ==========================================================================

    protected function sendWeeklyAdminReportEmail($notification, $data)
    {
        $email = \Config\Services::email();

        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($notification['recipient_email']);
        $email->setSubject('Relatório Semanal - DoarFazBem');

        $htmlMessage = $this->buildWeeklyAdminReportTemplate($data, $notification['recipient_name']);
        $email->setMessage($htmlMessage);

        if (!$email->send()) {
            throw new \Exception('Falha ao enviar email: ' . $email->printDebugger(['headers']));
        }
    }

    protected function buildWeeklyAdminReportTemplate($data, $recipientName)
    {
        $totalRaised = number_format($data['total_raised_week'], 2, ',', '.');
        $periodStart = $data['period_start'];
        $periodEnd = $data['period_end'];

        // Construir lista de campanhas ativas
        $campaignsList = '';
        if (!empty($data['campaigns_progress'])) {
            foreach ($data['campaigns_progress'] as $campaign) {
                $raised = number_format($campaign['raised'], 2, ',', '.');
                $goal = number_format($campaign['goal'], 2, ',', '.');
                $percentage = $campaign['percentage'];
                $milestone = $campaign['milestone'];

                // Cor baseada no progresso
                $barColor = '#10b981'; // verde
                if ($percentage < 25) {
                    $barColor = '#ef4444'; // vermelho
                } elseif ($percentage < 50) {
                    $barColor = '#f59e0b'; // laranja
                } elseif ($percentage < 75) {
                    $barColor = '#3b82f6'; // azul
                }

                $campaignsList .= <<<CAMPAIGN
                <div style="background: white; padding: 20px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid {$barColor};">
                    <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px;">{$campaign['title']}</h3>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: #6b7280;">Arrecadado:</span>
                        <span style="color: #1f2937; font-weight: bold;">R$ {$raised} / R$ {$goal}</span>
                    </div>
                    <div style="background: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden;">
                        <div style="background: {$barColor}; height: 100%; width: {$percentage}%; transition: width 0.3s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 13px;">
                        <span style="color: {$barColor}; font-weight: bold;">{$percentage}% alcançado</span>
                        <span style="color: #6b7280;">Marco: {$milestone}%</span>
                        <span style="color: #6b7280;">{$campaign['donors_count']} doadores</span>
                    </div>
                </div>
CAMPAIGN;
            }
        } else {
            $campaignsList = '<p style="color: #6b7280; text-align: center; padding: 20px;">Nenhuma campanha ativa no momento.</p>';
        }

        // Construir lista de campanhas concluídas
        $completedList = '';
        if (!empty($data['completed_campaigns_list'])) {
            foreach ($data['completed_campaigns_list'] as $completed) {
                $raisedCompleted = number_format($completed['raised'], 2, ',', '.');
                $completedList .= <<<COMPLETED
                <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                    <strong>{$completed['title']}</strong>: R$ {$raisedCompleted}
                </li>
COMPLETED;
            }
            $completedList = '<ul style="list-style: none; padding: 0; margin: 0;">' . $completedList . '</ul>';
        } else {
            $completedList = '<p style="color: #6b7280;">Nenhuma campanha concluída esta semana.</p>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">

    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 28px;">📊 Relatório Semanal</h1>
        <p style="color: rgba(255, 255, 255, 0.9); margin: 10px 0 0 0; font-size: 16px;">
            {$periodStart} - {$periodEnd}
        </p>
    </div>

    <!-- Content -->
    <div style="background: #f3f4f6; padding: 30px 20px;">
        <div style="max-width: 600px; margin: 0 auto;">

            <p style="color: #4b5563; font-size: 16px;">Olá <strong>{$recipientName}</strong>,</p>
            <p style="color: #6b7280; font-size: 14px;">Aqui está o resumo das atividades da plataforma na última semana:</p>

            <!-- Estatísticas Gerais -->
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 20px 0; color: #1f2937; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    Estatísticas Gerais
                </h2>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px;">
                        <div style="font-size: 28px; font-weight: bold; color: white;">{$data['new_donations']}</div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.9);">Novas Doações</div>
                    </div>

                    <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 8px;">
                        <div style="font-size: 28px; font-weight: bold; color: white;">R$ {$totalRaised}</div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.9);">Total Arrecadado</div>
                    </div>

                    <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px;">
                        <div style="font-size: 28px; font-weight: bold; color: white;">{$data['new_campaigns']}</div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.9);">Novas Campanhas</div>
                    </div>

                    <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px;">
                        <div style="font-size: 28px; font-weight: bold; color: white;">{$data['active_campaigns_count']}</div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.9);">Campanhas Ativas</div>
                    </div>
                </div>
            </div>

            <!-- Progresso das Campanhas Ativas -->
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 20px 0; color: #1f2937; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    Progresso das Campanhas Ativas
                </h2>
                {$campaignsList}
            </div>

            <!-- Campanhas Concluídas -->
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 20px 0; color: #1f2937; font-size: 20px; border-bottom: 2px solid #10b981; padding-bottom: 10px;">
                    🎉 Campanhas Concluídas Esta Semana ({$data['completed_campaigns']})
                </h2>
                {$completedList}
            </div>

            <!-- CTA -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="". base_url('admin/dashboard') ."" style="background: #667eea; color: white; padding: 14px 40px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600; font-size: 16px;">
                    Ver Dashboard Completo
                </a>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div style="text-align: center; padding: 30px 20px; color: #9ca3af; font-size: 13px;">
        <p style="margin: 0;">DoarFazBem - Painel Administrativo</p>
        <p style="margin: 5px 0 0 0;">Este é um email automático enviado semanalmente</p>
    </div>

</body>
</html>
HTML;
    }
}
