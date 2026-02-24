<?php

namespace App\Services;

use App\Models\CampaignUpdateModel;
use App\Models\CampaignModel;
use CodeIgniter\Email\Email;

/**
 * Email Notification Service
 * Gerencia envio de emails de notificação
 */
class EmailNotificationService
{
    protected $email;
    protected $updateModel;
    protected $campaignModel;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->updateModel = new CampaignUpdateModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Enviar notificação de atualização de campanha
     */
    public function sendCampaignUpdate($updateId, $recipientEmail, $recipientName, $unsubscribeToken)
    {
        try {
            // Buscar dados da atualização
            $update = $this->updateModel->find($updateId);
            if (!$update) {
                throw new \Exception('Atualização não encontrada');
            }

            // Buscar dados da campanha
            $campaign = $this->campaignModel->find($update['campaign_id']);
            if (!$campaign) {
                throw new \Exception('Campanha não encontrada');
            }

            // Configurar email
            $this->email->setFrom(
                getenv('email.fromEmail'),
                getenv('email.fromName')
            );
            $this->email->setTo($recipientEmail);
            $this->email->setSubject("Nova Atualização: {$campaign['title']}");

            // Corpo do email
            $message = $this->buildEmailTemplate([
                'recipient_name' => $recipientName,
                'campaign_title' => $campaign['title'],
                'campaign_slug' => $campaign['slug'],
                'update_title' => $update['title'],
                'update_content' => $update['content'],
                'update_date' => date('d/m/Y H:i', strtotime($update['created_at'])),
                'unsubscribe_url' => base_url("notifications/unsubscribe/{$unsubscribeToken}"),
            ]);

            $this->email->setMessage($message);

            // Enviar
            if ($this->email->send()) {
                log_message('info', "Email de notificação enviado para {$recipientEmail}");
                return true;
            } else {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', "Falha ao enviar email: {$error}");
                throw new \Exception("Falha ao enviar email: {$error}");
            }

        } catch (\Exception $e) {
            log_message('error', 'EmailNotificationService: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Template HTML do email
     */
    protected function buildEmailTemplate($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .update-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10B981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .update-title {
            font-size: 18px;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 10px;
        }
        .update-content {
            color: #374151;
            line-height: 1.8;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #10B981;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💚 DoarFazBem</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Nova Atualização da Campanha</p>
        </div>

        <div class="content">
            <p>Olá, <strong>{$data['recipient_name']}</strong>!</p>

            <p>A campanha <strong>{$data['campaign_title']}</strong> que você apoia acaba de postar uma nova atualização:</p>

            <div class="update-box">
                <div class="update-title">{$data['update_title']}</div>
                <div style="font-size: 12px; color: #6b7280; margin-bottom: 15px;">
                    📅 {$data['update_date']}
                </div>
                <div class="update-content">
                    {$data['update_content']}
                </div>
            </div>

            <div style="text-align: center;">
                <a href="https://doarfazbem.ai/campaigns/{$data['campaign_slug']}" class="btn">
                    Ver Campanha Completa
                </a>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #6b7280;">
                Obrigado por fazer parte desta história! Sua contribuição faz toda a diferença. ❤️
            </p>
        </div>

        <div class="footer">
            <p>
                Você está recebendo este email porque apoiou a campanha <strong>{$data['campaign_title']}</strong>.
            </p>
            <p>
                Não quer mais receber notificações desta campanha?<br>
                <a href="{$data['unsubscribe_url']}">Cancelar inscrição</a>
            </p>
            <p style="margin-top: 15px;">
                © 2025 DoarFazBem - A plataforma de crowdfunding mais justa do Brasil
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
