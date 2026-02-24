<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Libraries\AsaasAPI;

/**
 * Payment Controller
 *
 * Gerencia processamento de pagamentos e doações
 */
class Payment extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $transactionModel;
    protected $userModel;
    protected $asaas;
    protected $session;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->transactionModel = new TransactionModel();
        $this->userModel = new UserModel();
        $this->asaas = new AsaasAPI();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    /**
     * Página de doação
     * GET /donate/{campaign_slug}
     */
    public function donate($campaignSlug)
    {
        $campaign = $this->campaignModel->getCampaignBySlug($campaignSlug);

        if (!$campaign) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        if ($campaign['status'] !== 'active') {
            return redirect()->to('/campaigns/' . $campaignSlug)->with('error', 'Esta campanha não está aceitando doações no momento.');
        }

        $data = [
            'title' => 'Doar para ' . $campaign['title'] . ' | DoarFazBem',
            'campaign' => $campaign
        ];

        return view('payment/donate', $data);
    }

    /**
     * Processar doação
     * POST /payment/process
     */
    public function process()
    {
        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'campaign_id' => 'required|integer',
            'amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[pix,credit_card,boleto]',
            'donor_name' => 'required|min_length[3]',
            'donor_email' => 'required|valid_email',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $campaignId = $this->request->getPost('campaign_id');
        $amount = $this->request->getPost('amount');
        $paymentMethod = $this->request->getPost('payment_method');
        $donorName = $this->request->getPost('donor_name');
        $donorEmail = $this->request->getPost('donor_email');
        $donorPhone = $this->request->getPost('donor_phone');
        $isAnonymous = $this->request->getPost('is_anonymous') ? true : false;
        $message = $this->request->getPost('message');

        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Criar cliente no Asaas (se não for usuário logado)
        $userId = $this->session->get('id');
        $asaasCustomerId = null;

        if ($userId) {
            $user = $this->userModel->find($userId);
            $asaasCustomerId = $user['asaas_customer_id'];
        }

        if (!$asaasCustomerId) {
            // Criar cliente no Asaas
            $customerData = [
                'name' => $donorName,
                'email' => $donorEmail,
                'phone' => $donorPhone,
                'cpfCnpj' => $this->request->getPost('cpf'),
            ];

            $asaasCustomer = $this->asaas->createCustomer($customerData);

            if (isset($asaasCustomer['id'])) {
                $asaasCustomerId = $asaasCustomer['id'];

                // Salvar ID do cliente Asaas no usuário (se logado)
                if ($userId) {
                    $this->userModel->update($userId, ['asaas_customer_id' => $asaasCustomerId]);
                }
            } else {
                log_message('error', 'Erro ao criar cliente Asaas: ' . json_encode($asaasCustomer));
                return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento. Tente novamente.');
            }
        }

        // Calcular taxas
        // Nota: A taxa de plataforma é gerenciada através do split payment no Asaas
        // Não calculamos platform_fee aqui, pois o split de 2% para não-médicas é automático
        $platformFee = 0; // Sem taxa adicional - split é gerenciado pelo Asaas
        $paymentGatewayFee = 0; // Asaas cobra diretamente
        $netAmount = $amount; // Valor total vai para a campanha, split é deduzido pelo Asaas

        // Criar doação no banco
        $donationData = [
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'donor_name' => $donorName,
            'donor_email' => $donorEmail,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'payment_gateway_fee' => $paymentGatewayFee,
            'net_amount' => $netAmount,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'is_anonymous' => $isAnonymous,
            'message' => $message,
        ];

        $donationId = $this->donationModel->insert($donationData);

        // Processar pagamento via Asaas
        $description = 'Doação para: ' . $campaign['title'];

        try {
            if ($paymentMethod === 'pix') {
                $payment = $this->asaas->createPixPayment($asaasCustomerId, $amount, $description);

                if (isset($payment['id'])) {
                    // Buscar QR Code
                    $qrCode = $this->asaas->getPixQrCode($payment['id']);

                    // Atualizar doação com dados do PIX
                    $this->donationModel->update($donationId, [
                        'asaas_payment_id' => $payment['id'],
                        'pix_qr_code' => $qrCode['encodedImage'] ?? null,
                        'pix_copy_paste' => $qrCode['payload'] ?? null,
                    ]);

                    return redirect()->to('/payment/pix/' . $donationId);
                }
            } elseif ($paymentMethod === 'boleto') {
                $dueDate = date('Y-m-d', strtotime('+3 days'));
                $payment = $this->asaas->createBoletoPayment($asaasCustomerId, $amount, $dueDate, $description);

                if (isset($payment['id'])) {
                    $this->donationModel->update($donationId, [
                        'asaas_payment_id' => $payment['id'],
                        'boleto_url' => $payment['bankSlipUrl'] ?? null,
                    ]);

                    return redirect()->to('/payment/boleto/' . $donationId);
                }
            } elseif ($paymentMethod === 'credit_card') {
                // Processar cartão de crédito
                $cardData = [
                    'customer' => $asaasCustomerId,
                    'billingType' => 'CREDIT_CARD',
                    'value' => $amount,
                    'dueDate' => date('Y-m-d'),
                    'description' => $description,
                    'creditCard' => [
                        'holderName' => $this->request->getPost('card_holder_name'),
                        'number' => $this->request->getPost('card_number'),
                        'expiryMonth' => $this->request->getPost('card_expiry_month'),
                        'expiryYear' => $this->request->getPost('card_expiry_year'),
                        'ccv' => $this->request->getPost('card_cvv')
                    ],
                    'creditCardHolderInfo' => [
                        'name' => $donorName,
                        'email' => $donorEmail,
                        'cpfCnpj' => $this->request->getPost('cpf'),
                        'postalCode' => $this->request->getPost('postal_code'),
                        'addressNumber' => $this->request->getPost('address_number'),
                        'phone' => $donorPhone
                    ]
                ];

                $payment = $this->asaas->createCreditCardPayment($cardData);

                if (isset($payment['id'])) {
                    $this->donationModel->update($donationId, [
                        'asaas_payment_id' => $payment['id'],
                    ]);

                    // Verificar status
                    if ($payment['status'] === 'CONFIRMED' || $payment['status'] === 'RECEIVED') {
                        $this->donationModel->markAsReceived($donationId);
                        $this->campaignModel->updateDonationStats($campaignId, $amount);

                        return redirect()->to('/payment/success/' . $donationId);
                    }
                }
            }

            // Erro genérico
            log_message('error', 'Erro ao criar pagamento Asaas: ' . json_encode($payment ?? []));
            return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento. Tente novamente.');

        } catch (\Exception $e) {
            log_message('error', 'Exceção ao processar pagamento: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao processar pagamento.');
        }
    }

    /**
     * Página de PIX
     * GET /payment/pix/{donation_id}
     */
    public function pix($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        $campaign = $this->campaignModel->find($donation['campaign_id']);

        $data = [
            'title' => 'Pagamento via PIX | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign
        ];

        return view('payment/pix', $data);
    }

    /**
     * Página de Boleto
     * GET /payment/boleto/{donation_id}
     */
    public function boleto($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        $campaign = $this->campaignModel->find($donation['campaign_id']);

        $data = [
            'title' => 'Pagamento via Boleto | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign
        ];

        return view('payment/boleto', $data);
    }

    /**
     * Página de sucesso
     * GET /payment/success/{donation_id}
     */
    public function success($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return redirect()->to('/')->with('error', 'Doação não encontrada.');
        }

        $campaign = $this->campaignModel->find($donation['campaign_id']);

        $data = [
            'title' => 'Doação Realizada com Sucesso! | DoarFazBem',
            'donation' => $donation,
            'campaign' => $campaign
        ];

        return view('payment/success', $data);
    }

    /**
     * Verificar status do pagamento (Ajax)
     * GET /payment/check-status/{donation_id}
     */
    public function checkStatus($donationId)
    {
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            return $this->response->setJSON(['error' => 'Doação não encontrada']);
        }

        // Buscar status atualizado no Asaas
        if ($donation['asaas_payment_id']) {
            $payment = $this->asaas->getPayment($donation['asaas_payment_id']);

            if (isset($payment['status'])) {
                $status = strtolower($payment['status']);

                if ($status === 'received' || $status === 'confirmed') {
                    $this->donationModel->markAsReceived($donationId);
                    $this->campaignModel->updateDonationStats($donation['campaign_id'], $donation['amount']);

                    return $this->response->setJSON([
                        'status' => 'received',
                        'redirect' => base_url('payment/success/' . $donationId)
                    ]);
                }
            }
        }

        return $this->response->setJSON(['status' => $donation['status']]);
    }
}
