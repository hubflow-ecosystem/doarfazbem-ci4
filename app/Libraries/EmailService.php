<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

/**
 * Serviço de Envio de Emails
 *
 * Gerencia o envio de emails para doadores e criadores de campanhas
 */
class EmailService
{
    protected $email;
    protected $config;

    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->config = config('Email');
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
     * Envia email de boas-vindas para novo usuário
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

            return $this->email->send();
        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email de boas-vindas: ' . $e->getMessage());
            return false;
        }
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
