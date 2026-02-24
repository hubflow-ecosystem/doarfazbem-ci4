<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\UserModel;
use App\Models\CampaignRewardModel;
use App\Models\CampaignMediaModel;

/**
 * Campaign Controller
 *
 * Gerencia campanhas de crowdfunding
 */
class Campaign extends BaseController
{
    protected $campaignModel;
    protected $donationModel;
    protected $session;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->donationModel = new DonationModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url', 'text']);
    }

    /**
     * Lista todas as campanhas
     * GET /campaigns
     */
    public function index()
    {
        $perPage = 12;
        $page = $this->request->getGet('page') ?? 1;
        $category = $this->request->getGet('category');
        $search = $this->request->getGet('search');

        // Filtros - busca com ou sem categoria
        $builder = $this->campaignModel->where('status', 'active');

        if ($search) {
            $builder = $builder->groupStart()
                ->like('title', $search)
                ->orLike('description', $search)
            ->groupEnd();
        }

        if ($category) {
            $builder = $builder->where('category', $category);
        }

        $total = $builder->countAllResults(false);
        $campaigns = $builder->orderBy('created_at', 'DESC')
            ->findAll($perPage, ($page - 1) * $perPage);

        // Atualizar cada campanha com estatísticas em tempo real
        foreach ($campaigns as &$campaign) {
            $campaignWithStats = $this->campaignModel->getCampaignWithStats($campaign['id']);
            if ($campaignWithStats) {
                // Substituir com dados atualizados em tempo real
                $campaign['current_amount'] = $campaignWithStats['current_amount'];
                $campaign['donors_count'] = $campaignWithStats['donors_count'];
                $campaign['percentage'] = $campaignWithStats['percentage'];
            }
        }

        $data = [
            'title' => 'Todas as Campanhas | DoarFazBem',
            'description' => 'Encontre campanhas para apoiar',
            'campaigns' => $campaigns,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
            'current_category' => $category,
            'search_term' => $search
        ];

        return view('campaigns/list', $data);
    }

    /**
     * Exibe campanha individual
     * GET /campaigns/{slug}
     */
    public function show($slug)
    {
        $campaignBasic = $this->campaignModel->getCampaignBySlug($slug);

        if (!$campaignBasic) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Incrementar visualizações
        $this->campaignModel->incrementViews($campaignBasic['id']);

        // MÉTODO CENTRALIZADO - busca campanha com todas as estatísticas calculadas em tempo real
        $campaign = $this->campaignModel->getCampaignWithStats($campaignBasic['id']);

        // Buscar doações confirmadas da campanha (últimas 20)
        $donationModel = new \App\Models\Donation();
        $allDonations = $donationModel->getByCampaign($campaign['id'], true);
        $donations = array_slice($allDonations, 0, 20);

        // Buscar informações do criador
        $userModel = new UserModel();
        $creator = $userModel->find($campaign['user_id']);

        // Buscar atualizações da campanha
        $updateModel = new \App\Models\CampaignUpdateModel();
        $updates = $updateModel->getByCampaign($campaign['id'], 5); // Últimas 5 atualizações

        // Buscar comentários da campanha
        $commentModel = new \App\Models\CampaignCommentModel();
        $comments = $commentModel->getByCampaign($campaign['id'], 20); // Últimos 20 comentários

        // Buscar recompensas da campanha
        $rewardModel = new CampaignRewardModel();
        $rewards = $rewardModel->getRewardsWithStats($campaign['id']);

        // Buscar galeria de mídia
        $mediaModel = new CampaignMediaModel();
        $media = $mediaModel->getMediaByCampaign($campaign['id']);

        // Parse highlights JSON
        $highlights = [];
        if (!empty($campaign['highlights'])) {
            $highlights = json_decode($campaign['highlights'], true) ?? [];
        }

        $data = [
            'title' => $campaign['title'] . ' | DoarFazBem',
            'description' => character_limiter(strip_tags($campaign['description']), 160),
            'campaign' => $campaign,
            'creator' => $creator,
            'donations' => $donations,
            'updates' => $updates,
            'comments' => $comments,
            'rewards' => $rewards,
            'media' => $media,
            'highlights' => $highlights
        ];

        return view('campaigns/show', $data);
    }

    /**
     * Formulário de criar campanha
     * GET /campaigns/create
     */
    public function create()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Você precisa fazer login para criar uma campanha.');
        }

        $userId = $this->session->get('id');

        // Verifica se usuário já tem conta Asaas
        $asaasAccountModel = new \App\Models\AsaasAccount();
        $existingAccount = $asaasAccountModel->getByUserId($userId);

        if (!$existingAccount) {
            // Não tem conta Asaas, redireciona para completar perfil
            return redirect()->to('/dashboard/complete-profile')
                ->with('info', 'Para criar campanhas, você precisa completar seu perfil primeiro.');
        }

        $data = [
            'title' => 'Criar Campanha | DoarFazBem',
            'description' => 'Crie sua campanha de crowdfunding',
        ];

        return view('campaigns/create', $data);
    }

    /**
     * Processar criação de campanha
     * POST /campaigns/create
     */
    public function store()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Verificar reCAPTCHA
        helper('recaptcha');
        $token = $this->request->getPost('recaptcha_token');

        if (!verify_recaptcha($token, 'create_campaign', 0.3)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Verificação de segurança falhou. Por favor, tente novamente.');
        }

        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'title' => 'required|min_length[10]|max_length[255]',
            'description' => 'required|min_length[50]',
            'category' => 'required|in_list[medica,social,criativa,negocio,educacao]',
            'campaign_type' => 'required|in_list[flexivel,tudo_ou_tudo,recorrente]',
            'goal_amount' => 'required|decimal|greater_than[0]',
            'end_date' => 'required|valid_date',
            'image' => 'uploaded[image]|max_size[image,2048]|is_image[image]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Validação adicional: Campanhas médicas não podem ser "Tudo ou Tudo"
        if ($this->request->getPost('category') === 'medica' && $this->request->getPost('campaign_type') === 'tudo_ou_tudo') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Campanhas médicas não podem usar o tipo "Tudo ou Tudo". Por favor, escolha "Flexível" ou "Recorrente".');
        }

        // Nota: Conta Asaas já foi criada na página de completar perfil
        // A verificação de que o usuário tem conta Asaas é feita no método create()

        // Upload da imagem
        $image = $this->request->getFile('image');
        $imageName = null;

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $image->move(WRITEPATH . '../public/uploads/campaigns', $imageName);
        }

        // Preparar dados
        $data = [
            'user_id' => $this->session->get('id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'campaign_type' => $this->request->getPost('campaign_type'),
            'goal_amount' => $this->request->getPost('goal_amount'),
            'end_date' => $this->request->getPost('end_date'),
            'image' => $imageName,
            'video_url' => $this->request->getPost('video_url'),
            'status' => 'pending', // Aguardando aprovação
            'current_amount' => 0,
        ];

        // Adicionar localização se fornecida
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');

        if (!empty($city) && !empty($state)) {
            $data['city'] = $city;
            $data['state'] = $state;
            $data['country'] = 'Brasil';

            // Geocoding para obter coordenadas
            $coords = $this->geocodeAddress($city, $state);
            if ($coords) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lng'];
            }
        }

        try {
            $campaignId = $this->campaignModel->insert($data);

            if ($campaignId) {
                $this->session->setFlashdata('success', '✅ Campanha criada com sucesso! Ela está em análise e será publicada em breve.');
                return redirect()->to('/dashboard/my-campaigns');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar campanha: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao criar campanha. Tente novamente.');
        }
    }

    /**
     * Converte endereço em coordenadas usando Google Maps Geocoding API
     */
    private function geocodeAddress(string $city, string $state, string $country = 'Brasil'): ?array
    {
        $apiKey = config('Google')->mapsApiKey;

        if (empty($apiKey)) {
            log_message('warning', 'Google Maps API key não configurada');
            return null;
        }

        $address = urlencode("$city, $state, $country");
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";

        try {
            $response = @file_get_contents($url);

            if ($response === false) {
                log_message('error', 'Geocoding: Falha ao conectar com Google Maps API');
                return null;
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                $location = $data['results'][0]['geometry']['location'];

                log_message('info', "Geocoding: $city, $state -> {$location['lat']}, {$location['lng']}");

                return [
                    'lat' => $location['lat'],
                    'lng' => $location['lng']
                ];
            } else {
                log_message('warning', "Geocoding: Falha para $city, $state - Status: {$data['status']}");
                return null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Geocoding: Exceção - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Formulário de editar campanha
     * GET /campaigns/edit/{id}
     */
    public function edit($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Verificar se é o dono da campanha
        if ($campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Você não tem permissão para editar esta campanha.');
        }

        $data = [
            'title' => 'Editar Campanha | DoarFazBem',
            'campaign' => $campaign
        ];

        return view('campaigns/edit', $data);
    }

    /**
     * Processar atualização de campanha
     * POST /campaigns/update/{id}
     */
    public function update($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Validação
        $validation = \Config\Services::validation();

        $validation->setRules([
            'title' => 'required|min_length[10]|max_length[255]',
            'description' => 'required|min_length[50]',
            'end_date' => 'required|valid_date',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Upload de nova imagem (opcional)
        $image = $this->request->getFile('image');
        $imageName = $campaign['image'];

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $image->move(WRITEPATH . '../public/uploads/campaigns', $imageName);

            // Deletar imagem antiga
            if ($campaign['image'] && file_exists(WRITEPATH . '../public/uploads/campaigns/' . $campaign['image'])) {
                unlink(WRITEPATH . '../public/uploads/campaigns/' . $campaign['image']);
            }
        }

        // Atualizar
        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'end_date' => $this->request->getPost('end_date'),
            'image' => $imageName,
            'video_url' => $this->request->getPost('video_url'),
        ];

        try {
            $this->campaignModel->update($id, $data);
            $this->session->setFlashdata('success', '✅ Campanha atualizada com sucesso!');
            return redirect()->to('/dashboard/my-campaigns');
        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar campanha: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar campanha.');
        }
    }

    /**
     * Deletar campanha
     * POST /campaigns/delete/{id}
     */
    public function delete($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Não permitir deletar se já houver doações
        if ($campaign['donors_count'] > 0) {
            return redirect()->back()->with('error', 'Não é possível deletar uma campanha que já recebeu doações.');
        }

        try {
            // Deletar imagem
            if ($campaign['image'] && file_exists(WRITEPATH . '../public/uploads/campaigns/' . $campaign['image'])) {
                unlink(WRITEPATH . '../public/uploads/campaigns/' . $campaign['image']);
            }

            $this->campaignModel->delete($id);
            $this->session->setFlashdata('success', 'Campanha deletada com sucesso.');
            return redirect()->to('/dashboard/my-campaigns');
        } catch (\Exception $e) {
            log_message('error', 'Erro ao deletar campanha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar campanha.');
        }
    }

    /**
     * Cria subconta Asaas se o usuário ainda não tiver uma
     * Retorna true se já existe ou foi criada com sucesso, false se houve erro
     */
    private function createAsaasAccountIfNeeded(int $userId): bool
    {
        $asaasAccountModel = new \App\Models\AsaasAccount();

        // Verifica se usuário já possui subconta
        $existingAccount = $asaasAccountModel->getByUserId($userId);

        if ($existingAccount) {
            log_message('info', "Subconta Asaas já existe para user_id {$userId}");
            return true; // Já tem subconta
        }

        // Busca dados do usuário
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            log_message('error', "Usuário {$userId} não encontrado ao criar subconta Asaas");
            return false;
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

        // Remove caracteres especiais do CPF/CNPJ
        $cpfCnpj = \App\Models\AsaasAccount::cleanCpfCnpj($cpfCnpj);

        // Valida CPF/CNPJ
        if (!\App\Models\AsaasAccount::validateCpfCnpj($cpfCnpj)) {
            log_message('error', "CPF/CNPJ inválido ao criar subconta Asaas: {$cpfCnpj}");
            $this->session->setFlashdata('error', 'CPF/CNPJ inválido. Por favor, verifique e tente novamente.');
            return false;
        }

        // Limpa telefone (apenas números)
        $phone = preg_replace('/[^0-9]/', '', $phone ?? '');

        // Limpa CEP (apenas números)
        $postalCode = preg_replace('/[^0-9]/', '', $postalCode ?? '');

        // Determina tipo de pessoa (PF ou PJ)
        $companyType = strlen($cpfCnpj) === 14 ? 'LEGAL_ENTITY' : 'INDIVIDUAL';

        // Prepara dados completos para a API Asaas
        $asaasData = [
            'name' => $user['name'],
            'email' => $user['email'],
            'cpf_cnpj' => $cpfCnpj,
            'mobile_phone' => $phone ?: null,
            'birth_date' => $birthDate ?: null,
            'postal_code' => $postalCode ?: null,
            'address' => $address ?: null,
            'address_number' => $addressNumber ?: null,
            'complement' => $addressComplement ?: null,
            'province' => $neighborhood ?: null,
            'city' => $addressCity ?: null,
            'state' => $addressState ?: null,
            'company_type' => $companyType,
        ];

        try {
            // Cria subconta via API Asaas
            $asaasLib = new \App\Libraries\AsaasLibrary();
            $result = $asaasLib->createAccount($asaasData);

            if (!$result['success']) {
                // Verifica se o erro é porque a conta já existe (CPF duplicado)
                $errors = $result['data']['errors'] ?? [];
                $isDuplicateCpf = false;

                foreach ($errors as $error) {
                    if (isset($error['code']) && $error['code'] === 'invalid_action') {
                        // CPF já cadastrado no Asaas - vamos buscar a conta existente
                        $isDuplicateCpf = true;
                        break;
                    }
                }

                if ($isDuplicateCpf) {
                    // Buscar conta existente pelo email
                    $existingResult = $asaasLib->getCustomerByEmail($user['email']);

                    if ($existingResult['success'] && !empty($existingResult['data']['data'])) {
                        // Pega a primeira conta encontrada
                        $existingAccountData = $existingResult['data']['data'][0];

                        // Salva no nosso banco
                        $accountData = [
                            'user_id' => $userId,
                            'asaas_account_id' => $existingAccountData['id'],
                            'asaas_wallet_id' => $existingAccountData['walletId'] ?? null,
                            'account_status' => 'active',
                            'cpf_cnpj' => $cpfCnpj,
                            'phone' => $phone ?? null,
                            'mobile_phone' => $phone ?? null,
                            'api_response' => json_encode($existingAccountData),
                        ];

                        $asaasAccountModel->insert($accountData);

                        log_message('info', "Subconta Asaas existente vinculada para user_id {$userId}: {$existingAccountData['id']}");

                        return true;
                    }
                }

                $errorMsg = json_encode($errors);
                log_message('error', "Erro ao criar subconta Asaas para user_id {$userId}: {$errorMsg}");
                $this->session->setFlashdata('error', 'Erro ao criar conta no gateway de pagamento. Verifique seus dados e tente novamente.');
                return false;
            }

            // Salva dados da subconta no banco com todos os campos
            $accountData = [
                'user_id' => $userId,
                'asaas_account_id' => $result['data']['id'],
                'asaas_wallet_id' => $result['data']['walletId'] ?? null,
                'account_status' => 'active',
                'cpf_cnpj' => $cpfCnpj,
                'phone' => $phone ?: null,
                'mobile_phone' => $phone ?: null,
                'address' => $address ?: null,
                'address_number' => $addressNumber ?: null,
                'complement' => $addressComplement ?: null,
                'province' => $neighborhood ?: null,
                'postal_code' => $postalCode ?: null,
                'api_response' => json_encode($result['data']),
            ];

            $asaasAccountModel->insert($accountData);

            log_message('info', "Subconta Asaas criada com sucesso para user_id {$userId}: {$result['data']['id']}");

            return true;

        } catch (\Exception $e) {
            log_message('error', "Exceção ao criar subconta Asaas para user_id {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista de doadores de uma campanha
     * GET /campaigns/{id}/donors
     */
    public function donors($id)
    {
        $campaign = $this->campaignModel->find($id);

        if (!$campaign) {
            return redirect()->to('/campaigns')->with('error', 'Campanha não encontrada.');
        }

        // Verificar se o usuário é o dono da campanha ou admin
        $userId = session()->get('id');
        $userRole = session()->get('role');

        if ($campaign['user_id'] != $userId && $userRole !== 'admin') {
            return redirect()->to('/campaigns/' . $id)->with('error', 'Você não tem permissão para ver os doadores desta campanha.');
        }

        // Buscar doações confirmadas
        $donationModel = new \App\Models\Donation();
        $donations = $donationModel
            ->where('campaign_id', $id)
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Doadores - ' . $campaign['title'],
            'campaign' => $campaign,
            'donations' => $donations
        ];

        return view('campaigns/donors', $data);
    }

    /**
     * Gerenciar recompensas da campanha
     * GET /campaigns/{id}/rewards
     */
    public function rewards($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        $rewardModel = new CampaignRewardModel();
        $rewards = $rewardModel->getRewardsWithStats($id);

        return view('campaigns/rewards', [
            'title' => 'Gerenciar Recompensas',
            'campaign' => $campaign,
            'rewards' => $rewards
        ]);
    }

    /**
     * Adicionar recompensa
     * POST /campaigns/{id}/rewards/add
     */
    public function addReward($campaignId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $rewardModel = new CampaignRewardModel();

        $data = [
            'campaign_id' => $campaignId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'min_amount' => $this->request->getPost('min_amount'),
            'max_quantity' => $this->request->getPost('max_quantity') ?: null,
            'delivery_date' => $this->request->getPost('delivery_date') ?: null,
        ];

        if ($rewardModel->insert($data)) {
            return redirect()->back()->with('success', 'Recompensa adicionada com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao adicionar recompensa.');
    }

    /**
     * Atualizar recompensa
     * POST /campaigns/rewards/{id}/update
     */
    public function updateReward($rewardId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rewardModel = new CampaignRewardModel();
        $reward = $rewardModel->find($rewardId);

        if (!$reward) {
            return redirect()->back()->with('error', 'Recompensa não encontrada.');
        }

        $campaign = $this->campaignModel->find($reward['campaign_id']);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'min_amount' => $this->request->getPost('min_amount'),
            'max_quantity' => $this->request->getPost('max_quantity') ?: null,
            'delivery_date' => $this->request->getPost('delivery_date') ?: null,
        ];

        if ($rewardModel->update($rewardId, $data)) {
            return redirect()->back()->with('success', 'Recompensa atualizada!');
        }

        return redirect()->back()->with('error', 'Erro ao atualizar recompensa.');
    }

    /**
     * Deletar recompensa
     * POST /campaigns/rewards/{id}/delete
     */
    public function deleteReward($rewardId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rewardModel = new CampaignRewardModel();
        $reward = $rewardModel->find($rewardId);

        if (!$reward) {
            return redirect()->back()->with('error', 'Recompensa não encontrada.');
        }

        $campaign = $this->campaignModel->find($reward['campaign_id']);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        if ($rewardModel->delete($rewardId)) {
            return redirect()->back()->with('success', 'Recompensa removida!');
        }

        return redirect()->back()->with('error', 'Erro ao remover recompensa.');
    }

    /**
     * Gerenciar mídia da campanha
     * GET /campaigns/{id}/media
     */
    public function media($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        $mediaModel = new CampaignMediaModel();
        $media = $mediaModel->getMediaByCampaign($id);

        return view('campaigns/media', [
            'title' => 'Gerenciar Mídia',
            'campaign' => $campaign,
            'media' => $media
        ]);
    }

    /**
     * Adicionar mídia
     * POST /campaigns/{id}/media/add
     */
    public function addMedia($campaignId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $mediaModel = new CampaignMediaModel();
        $type = $this->request->getPost('type');

        if ($type === 'video') {
            $url = $this->request->getPost('video_url');
            $thumbnail = $mediaModel->getYouTubeThumbnail($url);
            $mediaModel->addVideo($campaignId, $url, $thumbnail);
        } else {
            // Upload de imagem
            $file = $this->request->getFile('image');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . '../public/uploads/campaigns/', $newName);
                $url = base_url('uploads/campaigns/' . $newName);
                $mediaModel->addImage($campaignId, $url);
            }
        }

        return redirect()->back()->with('success', 'Mídia adicionada!');
    }

    /**
     * Deletar mídia
     * POST /campaigns/media/{id}/delete
     */
    public function deleteMedia($mediaId)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $mediaModel = new CampaignMediaModel();
        $media = $mediaModel->find($mediaId);

        if (!$media) {
            return redirect()->back()->with('error', 'Mídia não encontrada.');
        }

        $campaign = $this->campaignModel->find($media['campaign_id']);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $mediaModel->delete($mediaId);
        return redirect()->back()->with('success', 'Mídia removida!');
    }

    /**
     * Atualizar highlights (Por que apoiar?)
     * POST /campaigns/{id}/highlights
     */
    public function updateHighlights($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()->with('error', 'Campanha não encontrada.');
        }

        $highlights = [];
        $titles = $this->request->getPost('highlight_title') ?? [];
        $descriptions = $this->request->getPost('highlight_description') ?? [];
        $icons = $this->request->getPost('highlight_icon') ?? [];

        // Lista de icones permitidos (whitelist)
        $allowedIcons = [
            'fas fa-check', 'fas fa-heart', 'fas fa-star', 'fas fa-shield-alt',
            'fas fa-hand-holding-heart', 'fas fa-users', 'fas fa-globe', 'fas fa-lightbulb'
        ];

        for ($i = 0; $i < count($titles); $i++) {
            if (!empty($titles[$i])) {
                // Sanitizar titulo e descricao (remover tags HTML)
                $title = strip_tags(trim($titles[$i]));
                $description = strip_tags(trim($descriptions[$i] ?? ''));
                $icon = $icons[$i] ?? 'fas fa-check';

                // Validar icone contra whitelist
                if (!in_array($icon, $allowedIcons)) {
                    $icon = 'fas fa-check';
                }

                // Limitar tamanho
                $title = mb_substr($title, 0, 100);
                $description = mb_substr($description, 0, 500);

                $highlights[] = [
                    'title' => $title,
                    'description' => $description,
                    'icon' => $icon
                ];
            }
        }

        $this->campaignModel->update($id, [
            'highlights' => json_encode($highlights)
        ]);

        return redirect()->back()->with('success', 'Destaques atualizados!');
    }

    /**
     * Pausar campanha
     * POST /campaigns/pause/{id}
     */
    public function pause($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        if ($campaign['status'] !== 'active') {
            return redirect()->back()->with('error', 'Apenas campanhas ativas podem ser pausadas.');
        }

        try {
            $this->campaignModel->update($id, ['status' => 'paused']);

            // Log de auditoria
            $auditModel = new \App\Models\AuditLogModel();
            $auditModel->log(
                'campaign_paused',
                'campaigns',
                $id,
                null,
                ['status' => 'paused', 'previous_status' => 'active'],
                $this->session->get('id')
            );

            $this->session->setFlashdata('success', 'Campanha pausada com sucesso. Ela não receberá novas doações enquanto estiver pausada.');
            return redirect()->to('/dashboard/my-campaigns');
        } catch (\Exception $e) {
            log_message('error', 'Erro ao pausar campanha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao pausar campanha.');
        }
    }

    /**
     * Reativar campanha pausada
     * POST /campaigns/resume/{id}
     */
    public function resume($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        if ($campaign['status'] !== 'paused') {
            return redirect()->back()->with('error', 'Apenas campanhas pausadas podem ser reativadas.');
        }

        // Verificar se a data de término ainda é válida
        if (strtotime($campaign['end_date']) < time()) {
            return redirect()->back()->with('error', 'Não é possível reativar a campanha. A data de término já passou.');
        }

        try {
            $this->campaignModel->update($id, ['status' => 'active']);

            // Log de auditoria
            $auditModel = new \App\Models\AuditLogModel();
            $auditModel->log(
                'campaign_resumed',
                'campaigns',
                $id,
                null,
                ['status' => 'active', 'previous_status' => 'paused'],
                $this->session->get('id')
            );

            $this->session->setFlashdata('success', 'Campanha reativada com sucesso! Ela voltará a receber doações.');
            return redirect()->to('/dashboard/my-campaigns');
        } catch (\Exception $e) {
            log_message('error', 'Erro ao reativar campanha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao reativar campanha.');
        }
    }

    /**
     * Encerrar campanha (marcar como concluída)
     * POST /campaigns/end/{id}
     */
    public function end($id)
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $campaign = $this->campaignModel->find($id);

        if (!$campaign || $campaign['user_id'] != $this->session->get('id')) {
            return redirect()->to('/dashboard/my-campaigns')->with('error', 'Campanha não encontrada.');
        }

        if (!in_array($campaign['status'], ['active', 'paused'])) {
            return redirect()->back()->with('error', 'Esta campanha não pode ser encerrada.');
        }

        try {
            $this->campaignModel->update($id, ['status' => 'completed']);

            // Log de auditoria
            $auditModel = new \App\Models\AuditLogModel();
            $auditModel->log(
                'campaign_ended',
                'campaigns',
                $id,
                null,
                ['status' => 'completed', 'previous_status' => $campaign['status'], 'ended_by' => 'creator'],
                $this->session->get('id')
            );

            $this->session->setFlashdata('success', 'Campanha encerrada com sucesso. Você ainda pode solicitar saque dos valores arrecadados.');
            return redirect()->to('/dashboard/my-campaigns');
        } catch (\Exception $e) {
            log_message('error', 'Erro ao encerrar campanha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao encerrar campanha.');
        }
    }
}
