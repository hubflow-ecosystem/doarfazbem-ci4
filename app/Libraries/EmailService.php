<?php

namespace App\Libraries;

use Config\Email as EmailConfig;
use App\Libraries\SMSFlowService;

/**
 * Serviço de Envio de Emails
 *
 * Gerencia o envio de emails para doadores e criadores de campanhas
 */
class EmailService
{
    protected $email;
    protected $config;
    protected $sms;

    public function __construct()
    {
        $this->email  = \Config\Services::email();
        $this->config = config('Email');
        $this->sms    = new SMSFlowService();
    }

    /**
     * Envia email de confirmação de doação
     */
    public function sendDonationConfirmation(array $donation, array $campaign): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($donation['donor_email']);
            $this->email->setSubject('Confirmação de Doação - ' . $campaign['title']);

            $paymentMethod = [
                'pix' => 'PIX',
                'boleto' => 'Boleto',
                'credit_card' => 'Cartão de Crédito'
            ][$donation['payment_method']] ?? 'N/A';

            $message = view('emails/donation_confirmation', [
                'donation' => $donation,
                'campaign' => $campaign,
                'payment_method' => $paymentMethod,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de doação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia email de agradecimento após pagamento confirmado
     */
    public function sendThankYouEmail(array $donation, array $campaign): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($donation['donor_email']);
            $this->email->setSubject('Obrigado pela sua doação! - ' . $campaign['title']);

            $message = view('emails/thank_you', [
                'donation' => $donation,
                'campaign' => $campaign,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de agradecimento: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifica criador sobre nova doação
     */
    public function notifyCreatorOfDonation(array $donation, array $campaign, array $creator): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($creator['email']);
            $this->email->setSubject('Nova doação recebida! - ' . $campaign['title']);

            $donorName = $donation['is_anonymous'] ? 'Doador Anônimo' : $donation['donor_name'];

            $message = view('emails/creator_new_donation', [
                'donation' => $donation,
                'campaign' => $campaign,
                'creator' => $creator,
                'donor_name' => $donorName,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao notificar criador: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia email de confirmação de assinatura
     */
    public function sendSubscriptionConfirmation(array $subscription, array $campaign): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($subscription['donor_email']);
            $this->email->setSubject('Assinatura Ativada - ' . $campaign['title']);

            $cycleLabels = [
                'monthly' => 'Mensal',
                'quarterly' => 'Trimestral',
                'semiannual' => 'Semestral',
                'yearly' => 'Anual'
            ];

            $message = view('emails/subscription_confirmation', [
                'subscription' => $subscription,
                'campaign' => $campaign,
                'cycle_label' => $cycleLabels[$subscription['cycle']] ?? 'Mensal',
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de assinatura: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia lembrete de renovação de assinatura
     */
    public function sendSubscriptionRenewalReminder(array $subscription, array $campaign): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($subscription['donor_email']);
            $this->email->setSubject('Lembrete: Renovação de Doação Recorrente - ' . $campaign['title']);

            $message = view('emails/subscription_renewal', [
                'subscription' => $subscription,
                'campaign' => $campaign,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar lembrete de renovação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia email de boas-vindas para novo usuário + SMS se tiver telefone
     */
    public function sendWelcomeEmail(array $user): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($user['email']);
            $this->email->setSubject('Bem-vindo ao DoarFazBem!');

            $message = view('emails/welcome', [
                'user' => $user,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            $emailResult = $this->email->send();

            // SMS de boas-vindas (paralelo ao email)
            if (!empty($user['phone']) && $this->sms->isConfigured()) {
                $this->sms->sendWelcome($user['phone'], $user['name']);
            }

            return $emailResult;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de boas-vindas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifica criador que a campanha foi aprovada (email + SMS)
     *
     * @param array $campaign Dados da campanha (title, slug)
     * @param array $creator  Dados do criador (email, name, phone)
     */
    public function sendCampaignApprovedNotification(array $campaign, array $creator): bool
    {
        // Email
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($creator['email']);
            $this->email->setSubject('Campanha Aprovada! - DoarFazBem');

            $message = view('emails/campaign_approved', [
                'campaign' => $campaign,
                'creator'  => $creator,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');
            $emailResult = $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email campanha aprovada: ' . $e->getMessage());
            $emailResult = false;
        }

        // SMS de campanha aprovada
        if (!empty($creator['phone']) && $this->sms->isConfigured()) {
            $this->sms->sendCampaignApproved(
                $creator['phone'],
                $creator['name'],
                $campaign['title'],
                $campaign['slug'] ?? ''
            );
        }

        return $emailResult;
    }

    /**
     * Notifica criador que a campanha foi rejeitada (email + SMS)
     *
     * @param array       $campaign Dados da campanha
     * @param array       $creator  Dados do criador (email, name, phone)
     * @param string|null $reason   Motivo da rejeição
     */
    public function sendCampaignRejectedNotification(array $campaign, array $creator, ?string $reason = null): bool
    {
        // Email
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($creator['email']);
            $this->email->setSubject('Campanha Reprovada - DoarFazBem');

            $message = view('emails/campaign_rejected', [
                'campaign' => $campaign,
                'creator'  => $creator,
                'reason'   => $reason,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');
            $emailResult = $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email campanha rejeitada: ' . $e->getMessage());
            $emailResult = false;
        }

        // SMS de campanha rejeitada
        if (!empty($creator['phone']) && $this->sms->isConfigured()) {
            $this->sms->sendCampaignRejected(
                $creator['phone'],
                $creator['name'],
                $campaign['title'],
                $reason
            );
        }

        return $emailResult;
    }

    /**
     * Envia email de recuperação de senha
     */
    public function sendPasswordResetEmail(array $user, string $token): bool
    {
        try {
            $this->email->setFrom($this->config->fromEmail, $this->config->fromName);
            $this->email->setTo($user['email']);
            $this->email->setSubject('Recuperação de Senha - DoarFazBem');

            $resetLink = base_url("auth/reset-password/{$token}");

            $message = view('emails/password_reset', [
                'user' => $user,
                'reset_link' => $resetLink,
            ]);

            $this->email->setMessage($message);
            $this->email->setMailType('html');

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de recuperação: ' . $e->getMessage());
            return false;
        }
    }
}
