<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\Subscription;
use App\Models\UserModel;

/**
 * Dashboard Controller
 *
 * Dashboard do usuário (criador de campanhas e doador)
 */
class Dashboard extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $subscriptionModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->subscriptionModel = new Subscription();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url', 'number']);
    }

    /**
     * Dashboard principal
     * GET /dashboard
     */
    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login primeiro.');
        }

        $userId = $this->session->get('id');

        // Buscar dados do usuário
        $user = $this->userModel->find($userId);

        // Buscar campanhas do usuário
        $myCampaigns = $this->campaignModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $totalRaised = 0;
        $activeCampaigns = 0;

        foreach ($myCampaigns as &$campaign) {
            // Buscar doações confirmadas da campanha
            $donations = $this->donationModel
                ->where('campaign_id', $campaign['id'])
                ->whereIn('status', ['confirmed', 'received'])
                ->findAll();

            $campaignRaised = array_sum(array_column($donations, 'amount'));
            $campaign['raised_amount'] = $campaignRaised;
            $campaign['donors_count'] = count($donations);
            $campaign['percentage'] = $campaign['goal_amount'] > 0
                ? ($campaignRaised / $campaign['goal_amount']) * 100
                : 0;

            // Calcular dias restantes
            $endDate = strtotime($campaign['end_date']);
            $today = strtotime(date('Y-m-d'));
            $campaign['days_left'] = max(0, floor(($endDate - $today) / 86400));

            $totalRaised += $campaignRaised;

            if ($campaign['status'] === 'active') {
                $activeCampaigns++;
            }
        }

        // Minhas doações
        $myDonations = $this->donationModel->getUserDonations($userId);
        $totalDonated = 0;

        foreach ($myDonations as $donation) {
            if ($donation['status'] === 'confirmed') {
                $totalDonated += $donation['amount'];
            }
        }

        // Minhas assinaturas
        $mySubscriptions = $this->subscriptionModel->getByUser($userId);
        $activeSubscriptions = count(array_filter($mySubscriptions, fn($s) => $s['status'] === 'active'));
        $monthlyCommitment = array_sum(
            array_column(
                array_filter($mySubscriptions, fn($s) => $s['status'] === 'active'),
                'amount'
            )
        );

        // Buscar conta Asaas do usuário
        $asaasAccountModel = new \App\Models\AsaasAccount();
        $asaasAccount = $asaasAccountModel->getByUserId($userId);

        log_message('error', 'DASHBOARD DEBUG: user_id=' . $userId . ', asaasAccount=' . json_encode($asaasAccount));

        // Se não encontrou na tabela asaas_accounts, verifica nos campos do usuário
        if (!$asaasAccount && !empty($user['asaas_account_id'])) {
            $asaasAccount = [
                'asaas_account_id' => $user['asaas_account_id'],
                'asaas_wallet_id' => $user['asaas_wallet_id'] ?? null,
                'account_status' => 'active',
            ];
        }

        $data = [
            'title' => 'Meu Dashboard | DoarFazBem',
            'total_campaigns' => count($myCampaigns),
            'active_campaigns' => $activeCampaigns,
            'total_raised' => $totalRaised,
            'total_donations' => count($myDonations),
            'total_donated' => $totalDonated,
            'active_subscriptions' => $activeSubscriptions,
            'monthly_commitment' => $monthlyCommitment,
            'recent_campaigns' => array_slice($myCampaigns, 0, 5),
            'recent_donations' => array_slice($myDonations, 0, 10),
            'asaas_account' => $asaasAccount,
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Minhas campanhas
     * GET /dashboard/my-campaigns
     */
    public function myCampaigns()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login primeiro.');
        }

        $userId = $this->session->get('id');
        $campaigns = $this->campaignModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Calcular estatísticas de cada campanha
        foreach ($campaigns as &$campaign) {
            $donations = $this->donationModel
                ->where('campaign_id', $campaign['id'])
                ->whereIn('status', ['confirmed', 'received'])
                ->findAll();

            $campaignRaised = array_sum(array_column($donations, 'amount'));
            $campaign['raised_amount'] = $campaignRaised;
            $campaign['donors_count'] = count($donations);
            $campaign['percentage'] = $campaign['goal_amount'] > 0
                ? ($campaignRaised / $campaign['goal_amount']) * 100
                : 0;

            $endDate = strtotime($campaign['end_date']);
            $today = strtotime(date('Y-m-d'));
            $campaign['days_left'] = max(0, floor(($endDate - $today) / 86400));
        }

        $data = [
            'title' => 'Minhas Campanhas | DoarFazBem',
            'campaigns' => $campaigns
        ];

        return view('dashboard/my_campaigns', $data);
    }

    /**
     * Minhas doações
     * GET /dashboard/my-donations
     */
    public function myDonations()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login primeiro.');
        }

        $userId = $this->session->get('id');
        $donations = $this->donationModel->getUserDonations($userId);

        // Adicionar nome da campanha a cada doação
        foreach ($donations as &$donation) {
            $campaign = $this->campaignModel->find($donation['campaign_id']);
            $donation['campaign_title'] = $campaign['title'] ?? 'Campanha não encontrada';
            $donation['campaign_image'] = $campaign['image'] ?? null;
        }

        // Buscar assinaturas
        $subscriptions = $this->subscriptionModel->getByUser($userId);

        // Adicionar nome da campanha
        foreach ($subscriptions as &$subscription) {
            $campaign = $this->campaignModel->find($subscription['campaign_id']);
            $subscription['campaign_title'] = $campaign['title'] ?? 'Campanha não encontrada';
            $subscription['campaign_image'] = $campaign['image'] ?? null;
        }

        // Calcular total doado
        $total_donated = 0;
        foreach ($donations as $donation) {
            if (in_array($donation['status'], ['received', 'paid', 'confirmed'])) {
                $total_donated += (float)$donation['amount'];
            }
        }

        $data = [
            'title' => 'Minhas Doações | DoarFazBem',
            'donations' => $donations,
            'subscriptions' => $subscriptions,
            'total_donated' => $total_donated,
        ];

        return view('dashboard/my_donations', $data);
    }

    /**
     * Ver detalhes de uma campanha
     * GET /dashboard/campaign/{id}
     */
    public function viewCampaign($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login primeiro.');
        }

        $userId = $this->session->get('id');
        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $userId) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Buscar doações
        $donations = $this->donationModel
            ->where('campaign_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Buscar assinaturas
        $subscriptions = $this->subscriptionModel
            ->where('campaign_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Calcular estatísticas
        $confirmedDonations = array_filter($donations, fn($d) => $d['status'] === 'confirmed');
        $totalRaised = array_sum(array_column($confirmedDonations, 'amount'));
        $donorsCount = count($confirmedDonations);
        $percentage = $campaign['goal_amount'] > 0
            ? ($totalRaised / $campaign['goal_amount']) * 100
            : 0;

        $activeSubscriptions = array_filter($subscriptions, fn($s) => $s['status'] === 'active');
        $monthlyRecurring = array_sum(array_column($activeSubscriptions, 'amount'));

        $endDate = strtotime($campaign['end_date']);
        $today = strtotime(date('Y-m-d'));
        $daysLeft = max(0, floor(($endDate - $today) / 86400));

        $data = [
            'title' => $campaign['title'] . ' - Dashboard | DoarFazBem',
            'campaign' => $campaign,
            'donations' => $donations,
            'subscriptions' => $subscriptions,
            'stats' => [
                'total_raised' => $totalRaised,
                'donors_count' => $donorsCount,
                'percentage' => $percentage,
                'days_left' => $daysLeft,
                'active_subscriptions' => count($activeSubscriptions),
                'monthly_recurring' => $monthlyRecurring,
            ],
        ];

        return view('dashboard/campaign_details', $data);
    }

    /**
     * Página para completar perfil (dados para Asaas)
     * GET /dashboard/complete-profile
     */
    public function completeProfile()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login primeiro.');
        }

        $userId = $this->session->get('id');
        $user = $this->userModel->find($userId);

        // Verifica se já tem conta Asaas (na tabela asaas_accounts ou nos campos do usuário)
        $asaasAccountModel = new \App\Models\AsaasAccount();
        $existingAccount = $asaasAccountModel->getByUserId($userId);

        // Verifica se tem dados válidos da conta
        $hasValidAccount = $existingAccount && !empty($existingAccount['asaas_account_id']);

        // Também verifica nos campos do usuário
        if (!$hasValidAccount && !empty($user['asaas_account_id'])) {
            $hasValidAccount = true;
        }

        // Se existe registro mas sem dados válidos, deleta para criar novo
        if ($existingAccount && !$hasValidAccount) {
            log_message('error', 'ASAAS DEBUG: Deletando registro inválido de asaas_accounts para user_id: ' . $userId);
            $asaasAccountModel->where('user_id', $userId)->delete();
            $existingAccount = null;
        }

        log_message('error', 'ASAAS DEBUG completeProfile: user_id=' . $userId . ', existingAccount=' . json_encode($existingAccount) . ', hasValidAccount=' . ($hasValidAccount ? 'true' : 'false'));

        if ($hasValidAccount) {
            // Já tem conta, verifica se veio de campanha existente ou não
            $hasCampaigns = $this->campaignModel->where('user_id', $userId)->countAllResults() > 0;
            if ($hasCampaigns) {
                return redirect()->to('/dashboard')->with('success', 'Sua conta de recebimentos já está configurada!');
            }
            return redirect()->to('/campaigns/create');
        }

        // Verifica se o perfil já está completo com todos os dados necessários
        $profileComplete = !empty($user['cpf']) &&
                          !empty($user['phone']) &&
                          !empty($user['birth_date']) &&
                          !empty($user['postal_code']) &&
                          !empty($user['address']) &&
                          !empty($user['address_number']) &&
                          !empty($user['province']) &&
                          !empty($user['city']) &&
                          !empty($user['state']);

        if ($profileComplete) {
            // Perfil completo, cria conta Asaas automaticamente
            $asaasLib = new \App\Libraries\AsaasLibrary();

            // Limpar CPF
            $cpfCnpj = preg_replace('/[^0-9]/', '', $user['cpf']);
            $phone = preg_replace('/[^0-9]/', '', $user['phone']);
            $postalCode = preg_replace('/[^0-9]/', '', $user['postal_code']);

            $accountData = [
                'name' => $user['name'],
                'email' => $user['email'],
                'cpf_cnpj' => $cpfCnpj,
                'birth_date' => $user['birth_date'],
                'mobile_phone' => $phone,
                'address' => $user['address'],
                'address_number' => $user['address_number'],
                'complement' => $user['address_complement'] ?? '',
                'province' => $user['province'],
                'city' => $user['city'],
                'state' => $user['state'],
                'postal_code' => $postalCode,
                'company_type' => strlen($cpfCnpj) === 11 ? 'INDIVIDUAL' : 'MEI',
                'income_value' => 1000, // Valor mínimo de renda exigido pelo Asaas
            ];

            $result = $asaasLib->createAccount($accountData);

            if ($result['success']) {
                // Salva conta Asaas
                $asaasAccountData = [
                    'user_id' => $userId,
                    'asaas_account_id' => $result['data']['id'],
                    'asaas_wallet_id' => $result['data']['walletId'] ?? null,
                    'account_status' => 'active',
                    'cpf_cnpj' => $cpfCnpj,
                    'phone' => $phone,
                    'mobile_phone' => $phone,
                    'address' => $user['address'],
                    'address_number' => $user['address_number'],
                    'complement' => $user['address_complement'] ?? '',
                    'province' => $user['province'],
                    'postal_code' => $postalCode,
                ];
                $asaasAccountModel->insert($asaasAccountData);

                // Atualiza user
                $this->userModel->update($userId, [
                    'asaas_account_id' => $result['data']['id'],
                    'asaas_wallet_id' => $result['data']['walletId'] ?? null,
                ]);

                // Se já tem campanhas, volta ao dashboard; senão, vai criar campanha
                $hasCampaigns = $this->campaignModel->where('user_id', $userId)->countAllResults() > 0;
                if ($hasCampaigns) {
                    return redirect()->to('/dashboard')
                        ->with('success', 'Conta de recebimentos criada com sucesso! Agora você pode receber doações.');
                }

                return redirect()->to('/campaigns/create')
                    ->with('success', 'Conta criada com sucesso! Agora você pode criar sua campanha.');
            } else {
                // Falhou, mostra o formulário para o usuário ajustar os dados
                log_message('error', 'Falha ao criar conta Asaas automaticamente: ' . json_encode($result));

                // Extrai mensagem de erro do Asaas
                $errorMessage = 'Erro ao criar conta de recebimentos. ';
                if (isset($result['data']['errors'][0]['description'])) {
                    $errorMessage .= $result['data']['errors'][0]['description'];
                } else {
                    $errorMessage .= 'Por favor, verifique seus dados e tente novamente.';
                }

                return redirect()->to('/dashboard/complete-profile')
                    ->with('error', $errorMessage)
                    ->withInput();
            }
        }

        $data = [
            'title' => 'Completar Perfil | DoarFazBem',
            'description' => 'Complete seus dados para criar campanhas',
            'user' => $user,
        ];

        return view('dashboard/complete_profile', $data);
    }

    /**
     * Processar dados do perfil para Asaas
     * POST /dashboard/complete-profile
     */
    public function saveProfile()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('id');
        $user = $this->userModel->find($userId);

        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'cpf_cnpj' => 'required|min_length[11]|max_length[18]',
            'phone' => 'required|min_length[10]|max_length[15]',
            'birth_date' => 'required|valid_date',
            'postal_code' => 'required|min_length[8]|max_length[9]',
            'address' => 'required|min_length[3]|max_length[255]',
            'address_number' => 'required|max_length[20]',
            'neighborhood' => 'required|min_length[2]|max_length[100]',
            'address_city' => 'required|min_length[2]|max_length[100]',
            'address_state' => 'required|exact_length[2]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Pega todos os dados do formulário
        $cpfCnpj = $this->request->getPost('cpf_cnpj');
        $phone = $this->request->getPost('phone');
        $birthDate = $this->request->getPost('birth_date');
        $postalCode = $this->request->getPost('postal_code');
        $address = $this->request->getPost('address');
        $addressNumber = $this->request->getPost('address_number');
        $addressComplement = $this->request->getPost('address_complement');
        $neighborhood = $this->request->getPost('neighborhood');
        $addressCity = $this->request->getPost('address_city');
        $addressState = $this->request->getPost('address_state');

        // Remove caracteres especiais
        $cpfCnpj = \App\Models\AsaasAccount::cleanCpfCnpj($cpfCnpj);
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $postalCode = preg_replace('/[^0-9]/', '', $postalCode);

        // Valida CPF/CNPJ
        $cpfLength = strlen($cpfCnpj);
        if ($cpfLength !== 11 && $cpfLength !== 14) {
            $type = $cpfLength < 14 ? 'CPF' : 'CNPJ';
            $expected = $cpfLength < 14 ? 11 : 14;
            return redirect()->back()
                ->withInput()
                ->with('error', "{$type} inválido. Deveria ter {$expected} dígitos, mas você inseriu {$cpfLength}. Por favor, verifique e tente novamente.");
        }

        if (!\App\Models\AsaasAccount::validateCpfCnpj($cpfCnpj)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'CPF/CNPJ inválido. Os dígitos verificadores não conferem. Por favor, verifique se digitou corretamente.');
        }

        // Determina tipo de pessoa (PF ou PJ)
        $companyType = strlen($cpfCnpj) === 14 ? 'LEGAL_ENTITY' : 'INDIVIDUAL';

        // Prepara dados para a API Asaas
        $asaasData = [
            'name' => $user['name'],
            'email' => $user['email'],
            'cpf_cnpj' => $cpfCnpj,
            'mobile_phone' => $phone,
            'birth_date' => $birthDate,
            'postal_code' => $postalCode,
            'address' => $address,
            'address_number' => $addressNumber,
            'complement' => $addressComplement ?: null,
            'province' => $neighborhood,
            'city' => $addressCity,
            'state' => $addressState,
            'company_type' => $companyType,
        ];

        try {
            // Cria subconta via API Asaas
            $asaasLib = new \App\Libraries\AsaasLibrary();
            $result = $asaasLib->createAccount($asaasData);

            $asaasAccountModel = new \App\Models\AsaasAccount();

            if (!$result['success']) {
                // Verifica se o erro é porque a conta já existe (CPF duplicado)
                // ou se é um erro 400 que pode indicar CPF já cadastrado
                $errors = $result['data']['errors'] ?? [];
                $httpCode = $result['http_code'] ?? 0;

                // Se for erro 400, tenta buscar conta existente pelo CPF
                if ($httpCode == 400) {
                    log_message('info', "Tentando buscar subconta existente para CPF {$cpfCnpj}");

                    // Primeiro tenta buscar por email
                    $existingResult = $asaasLib->getCustomerByEmail($user['email']);

                    if ($existingResult['success'] && !empty($existingResult['data']['data'])) {
                        $existingAccountData = $existingResult['data']['data'][0];

                        $accountData = [
                            'user_id' => $userId,
                            'asaas_account_id' => $existingAccountData['id'],
                            'asaas_wallet_id' => $existingAccountData['walletId'] ?? null,
                            'account_status' => 'active',
                            'cpf_cnpj' => $cpfCnpj,
                            'phone' => $phone,
                            'mobile_phone' => $phone,
                            'address' => $address,
                            'address_number' => $addressNumber,
                            'complement' => $addressComplement,
                            'province' => $neighborhood,
                            'postal_code' => $postalCode,
                            'api_response' => json_encode($existingAccountData),
                        ];

                        $asaasAccountModel->insert($accountData);

                        return redirect()->to('/campaigns/create')
                            ->with('success', 'Perfil completado com sucesso! Conta existente vinculada.');
                    }

                    // Se não encontrou por email, mostra erro específico sobre CPF duplicado
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Este CPF/CNPJ já está cadastrado no gateway de pagamento com outro email. Se você é o proprietário, entre em contato com o suporte.');
                }

                // Extrai mensagem de erro detalhada
                $errorMessages = [];
                foreach ($errors as $error) {
                    if (isset($error['description'])) {
                        $errorMessages[] = $error['description'];
                    }
                }

                $errorMsg = !empty($errorMessages) ? implode(' ', $errorMessages) : json_encode($result);
                log_message('error', "Erro ao criar subconta Asaas para user_id {$userId}: {$errorMsg}");

                // Mostra mensagem mais amigável para o usuário
                $userError = !empty($errorMessages)
                    ? 'Erro do gateway: ' . implode(' | ', $errorMessages)
                    : 'Erro ao criar conta no gateway de pagamento. Verifique seus dados e tente novamente.';

                return redirect()->back()
                    ->withInput()
                    ->with('error', $userError);
            }

            // Salva dados da subconta no banco
            $accountData = [
                'user_id' => $userId,
                'asaas_account_id' => $result['data']['id'],
                'asaas_wallet_id' => $result['data']['walletId'] ?? null,
                'account_status' => 'active',
                'cpf_cnpj' => $cpfCnpj,
                'phone' => $phone,
                'mobile_phone' => $phone,
                'address' => $address,
                'address_number' => $addressNumber,
                'complement' => $addressComplement,
                'province' => $neighborhood,
                'postal_code' => $postalCode,
                'api_response' => json_encode($result['data']),
            ];

            $asaasAccountModel->insert($accountData);

            log_message('info', "Subconta Asaas criada com sucesso para user_id {$userId}: {$result['data']['id']}");

            return redirect()->to('/campaigns/create')
                ->with('success', 'Perfil completado com sucesso! Agora você pode criar sua campanha.');

        } catch (\Exception $e) {
            log_message('error', "Exceção ao criar subconta Asaas para user_id {$userId}: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro inesperado. Por favor, tente novamente.');
        }
    }
}
