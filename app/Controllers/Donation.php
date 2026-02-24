<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\Donation as DonationModel;
use App\Models\AsaasAccount;
use App\Models\CampaignRewardModel;
use App\Libraries\AsaasService;

/**
 * Donation Controller
 *
 * Gerencia doações e pagamentos via Asaas
 */
class Donation extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $asaasAccountModel;
    protected $asaasService;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->asaasAccountModel = new AsaasAccount();
        $this->asaasService = new AsaasService();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    /**
     * Página de checkout da doação
     * GET /campaigns/{id}/donate
     */
    public function checkout($campaignId)
    {
        // Usar getCampaignWithStats para obter estatísticas em tempo real
        $campaign = $this->campaignModel->getCampaignWithStats($campaignId);

        if (!$campaign) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Verifica se campanha está ativa
        if ($campaign['status'] !== 'active') {
            return redirect()->to('/campaigns/' . $campaign['slug'])
                ->with('error', 'Esta campanha não está recebendo doações no momento.');
        }

        // Busca dados do criador da campanha
        $creatorAccount = $this->asaasAccountModel->getByUserId($campaign['user_id']);

        if (!$creatorAccount && ENVIRONMENT === 'production') {
            // Em produção, bloqueia doações sem conta Asaas
            log_message('error', "Campanha {$campaignId} sem subconta Asaas");
            return redirect()->to('/campaigns/' . $campaign['slug'])
                ->with('error', 'Esta campanha ainda não está configurada para receber doações.');
        }

        // Em desenvolvimento, apenas loga mas permite continuar
        if (!$creatorAccount) {
            log_message('warning', "Campanha {$campaignId} sem subconta Asaas - MODO DESENVOLVIMENTO");
        }

        // Calcula porcentagem
        $campaign['percentage'] = $this->campaignModel->getPercentage($campaignId);

        // Buscar dados do usuário logado
        $userData = null;
        if ($this->session->get('isLoggedIn')) {
            $userModel = new \App\Models\UserModel();
            $userData = $userModel->find($this->session->get('id'));
        }

        // Verificar se há recompensa selecionada
        $rewardId = $this->request->getGet('reward');
        $selectedReward = null;
        $minAmount = 10.00; // Valor mínimo padrão

        if ($rewardId) {
            $rewardModel = new CampaignRewardModel();
            $selectedReward = $rewardModel->find($rewardId);

            // Validar se recompensa pertence à campanha e está disponível
            if ($selectedReward && $selectedReward['campaign_id'] == $campaignId) {
                if (!$rewardModel->isAvailable($rewardId)) {
                    return redirect()->to('/campaigns/' . $campaign['slug'])
                        ->with('error', 'Esta recompensa não está mais disponível.');
                }
                $minAmount = (float) $selectedReward['min_amount'];
            } else {
                $selectedReward = null;
            }
        }

        // Buscar todas as recompensas da campanha
        $rewardModel = new CampaignRewardModel();
        $rewards = $rewardModel->getRewardsWithStats($campaignId);

        $data = [
            'title' => 'Doar para ' . $campaign['title'] . ' | DoarFazBem',
            'description' => 'Faça sua doação e ajude esta causa',
            'campaign' => $campaign,
            'user' => $userData,
            'rewards' => $rewards,
            'selectedReward' => $selectedReward,
            'minAmount' => $minAmount,
        ];

        return view('donations/checkout', $data);
    }

    /**
     * Processa a doação
     * POST /donations/process
     */
    public function process()
    {
        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'campaign_id' => 'required|integer',
            'donor_name' => 'required|min_length[3]|max_length[255]',
            'donor_email' => 'required|valid_email',
            'donor_cpf' => 'permit_empty|min_length[11]|max_length[14]',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[pix,credit_card,boleto]',
            'is_anonymous' => 'permit_empty',
            'donor_pays_fees' => 'permit_empty',
            'message' => 'permit_empty|max_length[500]',
            'reward_id' => 'permit_empty|integer',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $campaignId = $this->request->getPost('campaign_id');
        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Busca subconta do criador
        $creatorAccount = $this->asaasAccountModel->getByUserId($campaign['user_id']);

        if (!$creatorAccount && ENVIRONMENT === 'production') {
            return redirect()->back()->with('error', 'Erro ao processar doação. Tente novamente.');
        }

        // Em desenvolvimento, apenas avisar mas permitir continuar
        if (!$creatorAccount) {
            log_message('warning', "Campanha {$campaignId} sem subconta Asaas - MODO DESENVOLVIMENTO");
        }

        // Dados da doação
        $donorName = $this->request->getPost('donor_name');
        $donorEmail = $this->request->getPost('donor_email');
        $donorCpf = $this->request->getPost('donor_cpf');
        $amount = (float) $this->request->getPost('amount');
        $paymentMethod = $this->request->getPost('payment_method');
        $isAnonymous = $this->request->getPost('is_anonymous') === '1';
        $donorPaysFees = $this->request->getPost('donor_pays_fees') === '1';
        $isRecurring = $this->request->getPost('is_recurring') === '1';
        $cycle = $this->request->getPost('cycle') ?? 'monthly';
        $message = $this->request->getPost('message');
        $rewardId = $this->request->getPost('reward_id') ?: null;

        // Validar recompensa se selecionada
        $selectedReward = null;
        if ($rewardId) {
            $rewardModel = new CampaignRewardModel();
            $selectedReward = $rewardModel->find($rewardId);

            // Validar se recompensa pertence à campanha
            if (!$selectedReward || $selectedReward['campaign_id'] != $campaignId) {
                return redirect()->back()->withInput()->with('error', 'Recompensa inválida.');
            }

            // Validar se recompensa está disponível
            if (!$rewardModel->isAvailable($rewardId)) {
                return redirect()->back()->withInput()->with('error', 'Esta recompensa não está mais disponível.');
            }

            // Validar valor mínimo da recompensa
            if ($amount < $selectedReward['min_amount']) {
                $minFormatted = number_format($selectedReward['min_amount'], 2, ',', '.');
                return redirect()->back()->withInput()->with('error', "O valor mínimo para esta recompensa é R$ {$minFormatted}.");
            }
        }

        // Validar valor mínimo (campanha da plataforma aceita R$ 5,00, demais R$ 10,00)
        $minAmount = ($campaign['slug'] === 'mantenha-a-plataforma-ativa') ? 5.00 : 10.00;
        if ($amount < $minAmount) {
            $minFormatted = number_format($minAmount, 2, ',', '.');
            return redirect()->back()->withInput()->with('error', "O valor mínimo para doação é R$ {$minFormatted}.");
        }

        // Validar CPF obrigatório
        if (empty($donorCpf)) {
            return redirect()->back()->withInput()->with('error', 'CPF é obrigatório para realizar doações.');
        }

        try {
            // Se for recorrente, processar como assinatura
            if ($isRecurring && $campaign['campaign_type'] === 'recorrente') {
                return $this->processSubscription($campaign, $donorName, $donorEmail, $donorCpf, $amount, $paymentMethod, $cycle, $message);
            }

            // CALCULAR TAXAS E VALORES ANTES DE CRIAR NO ASAAS
            // Nota: Não há mais taxa adicional de plataforma na doação
            // O split de 2% para campanhas não-médicas é gerenciado automaticamente pelo Asaas
            $gatewayFee = 0;
            if ($donorPaysFees) {
                if ($paymentMethod === 'pix') $gatewayFee = 0.95;
                elseif ($paymentMethod === 'boleto') $gatewayFee = 0.99;
                elseif ($paymentMethod === 'credit_card') $gatewayFee = 0.49 + ($amount * 0.0199);
            }

            $chargedAmount = $donorPaysFees ? ceil($amount + $gatewayFee) : $amount;
            $netAmount = $amount - ($donorPaysFees ? 0 : $gatewayFee);

            // Caso contrário, processar como doação única (código original)
            // 1. Criar ou atualizar customer no Asaas (sandbox ou produção)
            $customerId = null;
            $asaasPaymentId = null;

            // Sempre usar Asaas (sandbox em dev, produção em prod)
            $customerData = [
                'name' => $donorName,
                'email' => $donorEmail,
            ];

            if ($donorCpf) {
                $customerData['cpfCnpj'] = preg_replace('/\D/', '', $donorCpf);
            }

            try {
                $customerResult = $this->asaasService->createOrUpdateCustomer($customerData);
                $customerId = $customerResult['id'];
            } catch (\Exception $e) {
                log_message('error', 'Erro ao criar customer Asaas: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Erro ao processar doação: ' . $e->getMessage());
            }

            // 2. Preparar dados do pagamento baseado no método
            // Data de vencimento varia conforme método
            // PIX: vence hoje (expira em 30 minutos automaticamente)
            // Boleto: vence em 3 dias
            // Cartão: vence hoje
            $dueDate = $paymentMethod === 'boleto'
                ? date('Y-m-d', strtotime('+3 days'))
                : date('Y-m-d'); // PIX e Cartão: hoje

            $paymentData = [
                'customer' => $customerId,
                'value' => $chargedAmount,
                'dueDate' => $dueDate,
                'description' => "Doação para: {$campaign['title']}",
                'externalReference' => "campaign_{$campaignId}_" . time(),
            ];

            // Configurar split payment se campanha tiver subconta
            if (!empty($creatorAccount['asaas_wallet_id'])) {
                // Split automático baseado no tipo de campanha:
                // - Campanhas médicas: 0% para plataforma (mas plataforma fica com centavos do arredondamento)
                // - Outras campanhas: 2% para plataforma (calculado sobre valor líquido pelo Asaas)
                if ($campaign['category'] === 'medica') {
                    // Médica: criador recebe tudo arredondado (plataforma fica com centavos)
                    $creatorAmount = floor($chargedAmount);
                    $paymentData['split'] = [
                        [
                            'walletId' => $creatorAccount['asaas_wallet_id'],
                            'fixedValue' => $creatorAmount
                        ]
                    ];
                } else {
                    // Não-médica: Asaas calcula 2% sobre valor líquido automaticamente
                    $paymentData['split'] = [
                        [
                            'walletId' => $creatorAccount['asaas_wallet_id'],
                            'percentualValue' => 98  // Criador recebe 98%, plataforma fica com 2%
                        ]
                    ];
                }
            }

            // 3. Criar cobrança no Asaas baseado no método (produção ou sandbox)
            $pixQrCode = null;
            $pixCopyPaste = null;
            $boletoUrl = null;
            $boletoBarcode = null;
            $paymentResult = null;

            // Usar Asaas tanto em produção quanto em desenvolvimento (sandbox)
            $useAsaas = true; // Sempre usar Asaas (sandbox em dev, produção em prod)

            if ($useAsaas) {
                try {
                    if ($paymentMethod === 'pix') {
                        $paymentResult = $this->asaasService->createPixPayment($paymentData);
                        $asaasPaymentId = $paymentResult['id'];

                        // Buscar QR Code do PIX
                        $pixData = $this->asaasService->getPixQrCode($asaasPaymentId);
                        if ($pixData && isset($pixData['encodedImage'])) {
                            $pixQrCode = $pixData['encodedImage'];
                            $pixCopyPaste = $pixData['payload'] ?? null;
                        }

                    } elseif ($paymentMethod === 'boleto') {
                        $paymentResult = $this->asaasService->createBoletoPayment($paymentData);
                        $asaasPaymentId = $paymentResult['id'];
                        $boletoUrl = $paymentResult['bankSlipUrl'] ?? null;
                        $boletoBarcode = $paymentResult['identificationField'] ?? null;

                    } else {
                        // Cartão de crédito - criar cobrança sem processar (vai processar depois com dados do cartão)
                        $cardPaymentData = [
                            'customer' => $customerId,
                            'billingType' => 'CREDIT_CARD',
                            'value' => $chargedAmount,
                            'dueDate' => date('Y-m-d'), // Cartão: vencimento hoje (será processado imediatamente)
                            'description' => "Doação para: {$campaign['title']}",
                            'externalReference' => "campaign_{$campaignId}_" . time(),
                        ];

                        if (!empty($paymentData['split'])) {
                            $cardPaymentData['split'] = $paymentData['split'];
                        }

                        // Criar payment sem dados do cartão (vai adicionar depois)
                        $paymentResult = $this->asaasService->createPayment($cardPaymentData);
                        $asaasPaymentId = $paymentResult['id'];
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Erro Asaas: ' . $e->getMessage());
                    return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
                }
            } else {
                log_message('info', 'MODO DESENVOLVIMENTO - Pulando criação de pagamento no Asaas');
                // Em desenvolvimento, criar IDs fictícios para testes
                $asaasPaymentId = 'dev_payment_' . time();
                if ($paymentMethod === 'pix') {
                    // QR Code fake para desenvolvimento - gerar imagem real
                    $pixCopyPaste = '00020126360014BR.GOV.BCB.PIX0114+55119999999995204000053039865802BR5913DoarFazBem6009SAO PAULO62070503***63041D3D';

                    // Gerar QR Code usando biblioteca ou imagem placeholder
                    // Por enquanto, usar uma imagem base64 válida de QR Code de teste
                    $pixQrCode = $this->generateDevelopmentQRCode($pixCopyPaste);
                }
            }

            // 4. Salvar doação no banco (taxas já calculadas acima)
            $donationData = [
                'campaign_id' => $campaignId,
                'user_id' => $this->session->get('isLoggedIn') ? $this->session->get('id') : null,
                'reward_id' => $rewardId,
                'donor_name' => $donorName,
                'donor_email' => $donorEmail,
                'amount' => $amount,
                'charged_amount' => $chargedAmount,
                'platform_fee' => 0, // Sem taxa adicional - split gerenciado pelo Asaas
                'payment_gateway_fee' => $gatewayFee,
                'net_amount' => $netAmount,
                'donor_pays_fees' => $donorPaysFees ? 1 : 0,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'is_anonymous' => $isAnonymous ? 1 : 0,
                'message' => $message,
                'notify_push' => $this->request->getPost('notify_push') === '1' ? 1 : 0,
                'notify_email' => $this->request->getPost('notify_email') === '1' ? 1 : 0,
                'donate_to_platform' => $this->request->getPost('donate_to_platform') === '1' ? 1 : 0,
                'asaas_payment_id' => $asaasPaymentId ?? null,
                'pix_qr_code' => $pixQrCode ?? null,
                'pix_copy_paste' => $pixCopyPaste ?? null,
                'boleto_url' => $boletoUrl ?? null,
                'boleto_barcode' => $boletoBarcode ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('donations')->insert($donationData);
            $donationId = $this->db->insertID();

            if (!$donationId) {
                log_message('error', 'Erro ao salvar doação no banco');
                return redirect()->back()->withInput()->with('error', 'Erro ao salvar doação.');
            }

            // 4.1 Incrementar contador da recompensa (se houver)
            if ($rewardId) {
                $rewardModel = new CampaignRewardModel();
                $rewardModel->claimReward($rewardId);
            }

            // 5. Salvar preferências de notificação
            if ($donationData['notify_email'] || $donationData['notify_push']) {
                $preferenceModel = new \App\Models\NotificationPreference();
                $preferenceModel->createOrUpdate([
                    'user_id' => $donationData['user_id'],
                    'donor_email' => $donorEmail,
                    'campaign_id' => $campaignId,
                    'notify_email' => $donationData['notify_email'],
                    'notify_push' => $donationData['notify_push'],
                    'push_token' => null, // Será preenchido via JavaScript se permitir push
                ]);
            }

            // 6. Salvar transação Asaas
            $transactionData = [
                'donation_id' => $donationId,
                'subscription_id' => null,
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_customer_id' => $customerId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'webhook_data' => json_encode($paymentResult),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('asaas_transactions')->insert($transactionData);

            // 7. Redirecionar conforme método de pagamento
            if ($paymentMethod === 'pix') {
                // Salvar dados do PIX temporariamente na sessão
                $this->session->setTempdata('pix_data', [
                    'qr_code' => $pixQrCode,
                    'copy_paste' => $pixCopyPaste,
                    'amount' => $amount
                ], 3600);
                return redirect()->to("/donations/pix/{$donationId}");
            } elseif ($paymentMethod === 'boleto') {
                $this->session->setTempdata('boleto_data', [
                    'url' => $boletoUrl,
                    'barcode' => $boletoBarcode,
                    'amount' => $amount
                ], 3600);
                return redirect()->to("/donations/boleto/{$donationId}");
            } else {
                // Para cartão de crédito, redirecionar para formulário de cartão
                return redirect()->to("/donations/credit-card/{$donationId}");
            }

        } catch (\Exception $e) {
            log_message('error', 'Exceção ao processar doação: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao processar doação. Tente novamente.');
        }
    }

    /**
     * Página de pagamento PIX
     * GET /donations/pix/{id}
     */
    public function pix($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        // Usar getCampaignWithStats para obter estatísticas em tempo real
        $campaign = $this->campaignModel->getCampaignWithStats($donation['campaign_id']);

        $data = [
            'title' => 'Pagamento PIX | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign,
        ];

        return view('donations/pix', $data);
    }

    /**
     * Página de boleto
     * GET /donations/boleto/{id}
     */
    public function boleto($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        $campaign = $this->campaignModel->find($donation['campaign_id']);

        $data = [
            'title' => 'Boleto Bancário | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign,
        ];

        return view('donations/boleto', $data);
    }

    /**
     * Página de pagamento com cartão
     * GET /donations/credit-card/{id}
     */
    public function creditCard($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        // Usar getCampaignWithStats para obter estatísticas em tempo real
        $campaign = $this->campaignModel->getCampaignWithStats($donation['campaign_id']);

        // Buscar dados do usuário logado para auto-preenchimento
        $userData = null;
        if ($this->session->get('isLoggedIn')) {
            $userModel = new \App\Models\UserModel();
            $userData = $userModel->find($this->session->get('id'));
        }

        $data = [
            'title' => 'Pagamento com Cartão | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign,
            'user' => $userData,
        ];

        return view('donations/credit_card', $data);
    }

    /**
     * Processa pagamento com cartão
     * POST /donations/process-card
     */
    public function processCard()
    {
        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'donation_id' => 'required|integer',
            'card_number' => 'required|min_length[13]|max_length[19]',
            'card_holder' => 'required|min_length[3]',
            'expiry_month' => 'required|exact_length[2]|integer|greater_than[0]|less_than[13]',
            'expiry_year' => 'required|exact_length[4]|integer',
            'cvv' => 'required|min_length[3]|max_length[4]',
            'installments' => 'required|integer|greater_than[0]|less_than[13]',
            // Dados do titular (obrigatórios para cartão)
            'holder_cpf' => 'required|min_length[11]',
            'holder_phone' => 'required|min_length[10]',
            'holder_postal_code' => 'required|min_length[8]',
            'holder_address' => 'required|min_length[3]',
            'holder_address_number' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', 'Por favor, verifique todos os dados obrigatórios.');
        }

        $donationId = $this->request->getPost('donation_id');
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        try {
            // Dados do cartão
            $cardNumber = str_replace(' ', '', $this->request->getPost('card_number'));
            $cardHolder = $this->request->getPost('card_holder');
            $expiryMonth = $this->request->getPost('expiry_month');
            $expiryYear = $this->request->getPost('expiry_year');
            $cvv = $this->request->getPost('cvv');
            $installments = (int) $this->request->getPost('installments');

            // Dados do titular do cartão (obrigatórios)
            $holderCpf = $this->request->getPost('holder_cpf');
            $holderPhone = $this->request->getPost('holder_phone');
            $holderPostalCode = $this->request->getPost('holder_postal_code');
            $holderAddress = $this->request->getPost('holder_address');
            $holderAddressNumber = $this->request->getPost('holder_address_number');
            $holderAddressComplement = $this->request->getPost('holder_address_complement');

            // Processar pagamento com cartão
            $paymentData = [
                'payment_id' => $donation['asaas_payment_id'],
                'card_number' => $cardNumber,
                'card_holder' => $cardHolder,
                'expiry_month' => $expiryMonth,
                'expiry_year' => $expiryYear,
                'cvv' => $cvv,
                'installment_count' => $installments,
                // Dados do titular
                'holder_name' => $donation['donor_name'],
                'holder_email' => $donation['donor_email'],
                'holder_cpf' => $holderCpf,
                'holder_phone' => $holderPhone,
                'holder_postal_code' => $holderPostalCode,
                'holder_address' => $holderAddress,
                'holder_address_number' => $holderAddressNumber,
                'holder_address_complement' => $holderAddressComplement,
            ];

            try {
                $result = $this->asaasService->payWithCreditCard($paymentData);

                // Atualizar doação e campanha
                $donation = $this->donationModel->find($donationId);

                // Atualizar status da doação
                $this->donationModel->update($donationId, [
                    'status' => 'received',
                    'paid_at' => date('Y-m-d H:i:s'),
                ]);

                // Atualizar valor arrecadado da campanha
                $campaignModel = new \App\Models\CampaignModel();
                $campaign = $campaignModel->find($donation['campaign_id']);

                if ($campaign) {
                    $currentRaised = $campaign['current_amount'] ?? 0;
                    $campaignModel->update($campaign['id'], [
                        'current_amount' => $currentRaised + $donation['amount'],
                    ]);
                }

                return redirect()->to("/donations/success/{$donationId}");

            } catch (\Exception $e) {
                log_message('error', 'Erro ao processar cartão Asaas: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            log_message('error', 'Exceção ao processar cartão: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento. Tente novamente.');
        }
    }

    /**
     * Página de sucesso
     * GET /donations/success/{id}
     */
    public function success($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        // Usar getCampaignWithStats para obter estatísticas em tempo real
        $campaign = $this->campaignModel->getCampaignWithStats($donation['campaign_id']);

        $data = [
            'title' => 'Doação Concluída | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign,
        ];

        return view('donations/success', $data);
    }

    /**
     * Consulta status do PIX via AJAX
     * GET /donations/pix-status/{id}
     */
    public function pixStatus($donationId)
    {
        // Buscar doacao para verificar propriedade
        $donation = $this->donationModel->find($donationId);
        if (!$donation) {
            return $this->response->setJSON(['success' => false, 'message' => 'Doacao nao encontrada']);
        }

        // Verificar se usuario logado e dono da doacao ou admin
        $userId = session()->get('user_id');
        $isAdmin = session()->get('is_admin');

        // Se nao for admin, verificar se e dono da doacao (pelo email ou user_id)
        if (!$isAdmin) {
            $isOwner = false;

            // Verificar por user_id se estiver logado
            if ($userId && isset($donation['user_id']) && $donation['user_id'] == $userId) {
                $isOwner = true;
            }

            // Verificar por session de doacao anonima (criada durante o processo de doacao)
            $sessionDonationId = session()->get('current_donation_id');
            if ($sessionDonationId == $donationId) {
                $isOwner = true;
            }

            if (!$isOwner) {
                log_message('warning', "Tentativa de acesso nao autorizado ao pixStatus: donation_id={$donationId}, user_id={$userId}");
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acesso nao autorizado']);
            }
        }

        // Buscar transação Asaas relacionada à doação
        $transaction = $this->db->table('asaas_transactions')
            ->where('donation_id', $donationId)
            ->get()
            ->getRowArray();

        if (!$transaction) {
            return $this->response->setJSON(['success' => false, 'message' => 'Transação não encontrada']);
        }

        // Consulta status no Asaas
        try {
            $paymentData = $this->asaasService->getPayment($transaction['asaas_payment_id']);
            $status = $paymentData['status'] ?? null;

            if (!$status) {
                return $this->response->setJSON(['success' => false, 'message' => 'Status não disponível']);
            }

            // Atualiza status se mudou para recebido/confirmado
            if (in_array($status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'])) {
                // Atualizar transação
                $this->db->table('asaas_transactions')
                    ->where('id', $transaction['id'])
                    ->update([
                        'status' => 'received',
                        'processed_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                // Atualizar doação
                $this->db->table('donations')
                    ->where('id', $donationId)
                    ->update([
                        'status' => 'received',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'confirmed',
                    'redirect' => base_url("donations/success/{$donationId}")
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'status' => strtolower($status),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao consultar status PIX: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Erro ao consultar status']);
        }
    }

    /**
     * Processa assinatura (doação recorrente)
     */
    private function processSubscription($campaign, $donorName, $donorEmail, $donorCpf, $amount, $paymentMethod, $cycle, $message)
    {
        // Validar que assinaturas só funcionam com cartão ou boleto
        if ($paymentMethod === 'pix') {
            return redirect()->back()->withInput()->with('error', 'Doações recorrentes não podem ser feitas via PIX. Use cartão de crédito ou boleto.');
        }

        // Criar customer
        $customerData = [
            'name' => $donorName,
            'email' => $donorEmail,
            'cpf_cnpj' => $donorCpf ? AsaasAccount::cleanCpfCnpj($donorCpf) : null,
        ];

        try {
            $customerResult = $this->asaasService->createOrUpdateCustomer($customerData);
            $customerId = $customerResult['id'];
        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar customer para subscription: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao criar cadastro: ' . $e->getMessage());
        }

        // Buscar subconta do criador
        $creatorAccount = $this->asaasAccountModel->getByUserId($campaign['user_id']);

        // Calcular split
        $campaignType = $this->getCampaignType($campaign);
        $splits = $this->asaasService->calculateSplit($amount, $campaignType, $creatorAccount['asaas_wallet_id']);

        // Converter ciclo para formato da API Asaas
        $asaasCycles = [
            'monthly' => 'MONTHLY',
            'quarterly' => 'QUARTERLY',
            'semiannual' => 'SEMIANNUALLY',
            'yearly' => 'YEARLY'
        ];
        $asaasCycle = $asaasCycles[$cycle] ?? 'MONTHLY';

        // Converter método de pagamento para formato Asaas
        $billingTypes = [
            'credit_card' => 'CREDIT_CARD',
            'boleto' => 'BOLETO'
        ];
        $billingType = $billingTypes[$paymentMethod] ?? 'CREDIT_CARD';

        // Calcular próxima data de vencimento (primeira cobrança em 7 dias)
        $subscriptionModel = new \App\Models\Subscription();
        $nextDueDate = date('Y-m-d', strtotime('+7 days'));

        // Criar assinatura no Asaas
        $subscriptionData = [
            'customer' => $customerId,
            'billing_type' => $billingType,
            'value' => $amount,
            'next_due_date' => $nextDueDate,
            'cycle' => $asaasCycle,
            'description' => "Doação recorrente para: {$campaign['title']}",
            'external_reference' => "campaign_{$campaign['id']}_subscription",
            'split' => $splits,
        ];

        try {
            $result = $this->asaasService->createSubscription($subscriptionData);

            // Salvar assinatura no banco
            $subscriptionId = $subscriptionModel->insert([
                'campaign_id' => $campaign['id'],
                'user_id' => $this->session->get('isLoggedIn') ? $this->session->get('id') : null,
                'donor_name' => $donorName,
                'donor_email' => $donorEmail,
                'donor_cpf' => $donorCpf,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'cycle' => $cycle,
                'status' => 'active',
                'asaas_subscription_id' => $result['id'],
                'asaas_customer_id' => $customerId,
                'next_due_date' => $result['nextDueDate'] ?? $nextDueDate,
                'started_at' => date('Y-m-d H:i:s'),
                'api_response' => json_encode($result),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar assinatura: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao criar assinatura: ' . $e->getMessage());
        }

        // Se for cartão de crédito, redirecionar para página de cadastro do cartão
        // (Asaas permite tokenizar cartão para uso recorrente)
        if ($paymentMethod === 'credit_card') {
            return redirect()->to("/donations/subscription-card/{$subscriptionId}")
                ->with('info', 'Agora precisamos dos dados do seu cartão para processar as cobranças automáticas.');
        }

        // Para boleto, mostrar página de sucesso
        return view('donations/subscription_success', [
            'title' => 'Assinatura Criada com Sucesso | DoarFazBem',
            'subscription' => $subscriptionModel->find($subscriptionId),
            'campaign' => $campaign,
        ]);
    }

    /**
     * Determina tipo da campanha para cálculo de taxa
     */
    private function getCampaignType(array $campaign): string
    {
        $category = strtolower($campaign['category'] ?? '');

        if (in_array($category, ['medica', 'saude'])) {
            return 'medical';
        } elseif ($category === 'social') {
            return 'social';
        }

        return 'other';
    }

    /**
     * Gera QR Code fake para ambiente de desenvolvimento
     * Usa a biblioteca SimpleQRCode ou retorna uma imagem placeholder
     */
    private function generateDevelopmentQRCode(string $pixCode): string
    {
        // Tentar usar biblioteca chillerlan/php-qrcode se disponível
        if (class_exists('\chillerlan\QRCode\QRCode')) {
            try {
                $qrcode = new \chillerlan\QRCode\QRCode();
                $qrImageData = $qrcode->render($pixCode);

                // Remover o prefixo data:image se já tiver
                if (strpos($qrImageData, 'data:image') === 0) {
                    return substr($qrImageData, strpos($qrImageData, ',') + 1);
                }

                return $qrImageData;
            } catch (\Exception $e) {
                log_message('warning', 'Erro ao gerar QR Code: ' . $e->getMessage());
            }
        }

        // Fallback: Retornar imagem SVG base64 com texto "PIX DESENVOLVIMENTO"
        $svg = '<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
            <rect width="300" height="300" fill="#ffffff"/>
            <rect x="20" y="20" width="260" height="260" fill="#000000" opacity="0.1"/>
            <text x="150" y="130" font-family="Arial" font-size="16" text-anchor="middle" fill="#333">
                QR CODE PIX
            </text>
            <text x="150" y="160" font-family="Arial" font-size="14" text-anchor="middle" fill="#666">
                DESENVOLVIMENTO
            </text>
            <text x="150" y="190" font-family="Arial" font-size="12" text-anchor="middle" fill="#999">
                Use o código Copia e Cola
            </text>
        </svg>';

        return base64_encode($svg);
    }
}
