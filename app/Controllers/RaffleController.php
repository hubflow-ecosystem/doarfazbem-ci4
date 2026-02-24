<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RaffleModel;
use App\Models\RafflePackageModel;
use App\Models\RaffleNumberModel;
use App\Models\RafflePurchaseModel;
use App\Models\RaffleRankingModel;
use App\Models\RaffleCampaignDistributionModel;
use App\Models\RaffleWinnerModel;
use App\Models\CampaignModel;
use App\Models\AuditLogModel;

/**
 * RaffleController
 *
 * Gerencia a exibição e compra de rifas
 */
class RaffleController extends BaseController
{
    protected $raffleModel;
    protected $packageModel;
    protected $numberModel;
    protected $purchaseModel;
    protected $rankingModel;

    public function __construct()
    {
        $this->raffleModel = new RaffleModel();
        $this->packageModel = new RafflePackageModel();
        $this->numberModel = new RaffleNumberModel();
        $this->purchaseModel = new RafflePurchaseModel();
        $this->rankingModel = new RaffleRankingModel();
    }

    /**
     * Página principal da rifa ativa
     */
    public function index()
    {
        $raffle = $this->raffleModel->getActiveRaffle();

        if (!$raffle) {
            return view('raffles/no_active', [
                'title' => 'Números da Sorte | DoarFazBem',
            ]);
        }

        return $this->show($raffle['slug']);
    }

    /**
     * Exibe detalhes de uma rifa específica
     */
    public function show(string $slug)
    {
        $raffle = $this->raffleModel->findBySlug($slug);

        if (!$raffle) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $packages = $this->packageModel->getPackagesByRaffle($raffle['id']);
        $stats = $this->raffleModel->getStats($raffle['id']);
        $ranking = $this->rankingModel->getRanking($raffle['id'], 10);

        // Busca campanhas ativas para seleção
        $campaignModel = new CampaignModel();
        $campaigns = $campaignModel->where('status', 'active')
            ->where('slug !=', 'mantenha-a-plataforma-ativa')
            ->orderBy('current_amount', 'DESC')
            ->limit(20)
            ->findAll();

        // Dados do usuário logado
        $userData = null;
        $userNumbers = [];
        $userRanking = null;

        if ($this->session->get('isLoggedIn')) {
            $userModel = new \App\Models\UserModel();
            $userData = $userModel->find($this->session->get('id'));
            $userNumbers = $this->numberModel->getUserNumbers($raffle['id'], $this->session->get('id'));
            $userRanking = $this->rankingModel->getUserRankingData($raffle['id'], $this->session->get('id'));
        }

        // Busca prêmios especiais/instantâneos cadastrados
        $db = \Config\Database::connect();
        $specialPrizes = $db->table('raffle_special_prizes')
            ->where('raffle_id', $raffle['id'])
            ->where('is_active', 1)
            ->orderBy('prize_amount', 'DESC')
            ->get()
            ->getResultArray();

        return view('raffles/show', [
            'title' => $raffle['title'] . ' | DoarFazBem',
            'raffle' => $raffle,
            'packages' => $packages,
            'stats' => $stats,
            'ranking' => $ranking,
            'campaigns' => $campaigns,
            'user' => $userData,
            'userNumbers' => $userNumbers,
            'userRanking' => $userRanking,
            'specialPrizes' => $specialPrizes,
        ]);
    }

    /**
     * Processa compra de cotas
     */
    public function purchase()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'raffle_id' => 'required|integer',
            'quantity' => 'required|integer|greater_than[0]',
            'buyer_name' => 'required|min_length[3]|max_length[255]',
            'buyer_email' => 'required|valid_email',
            'buyer_cpf' => 'required|min_length[11]|max_length[14]',
            'buyer_phone' => 'permit_empty|min_length[10]|max_length[20]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $raffleId = (int) $this->request->getPost('raffle_id');
        $quantity = (int) $this->request->getPost('quantity');
        $buyerName = $this->request->getPost('buyer_name');
        $buyerEmail = $this->request->getPost('buyer_email');
        $buyerCpf = preg_replace('/[^0-9]/', '', $this->request->getPost('buyer_cpf'));
        $buyerPhone = $this->request->getPost('buyer_phone');
        $campaignIds = $this->request->getPost('campaigns') ?? [];

        // Validar CPF com checksum
        if (!\App\Models\AsaasAccount::validateCpf($buyerCpf)) {
            return redirect()->back()->withInput()->with('error', 'CPF invalido. Verifique os digitos e tente novamente.');
        }

        // Validar rifa
        $raffle = $this->raffleModel->find($raffleId);
        if (!$raffle || $raffle['status'] !== 'active') {
            return redirect()->back()->with('error', 'Esta rifa não está disponível para compra.');
        }

        // Verificar disponibilidade
        $available = $this->numberModel->countAvailable($raffleId);
        if ($available < $quantity) {
            return redirect()->back()->with('error', "Apenas {$available} números disponíveis.");
        }

        // Calcular preço
        $pricing = $this->packageModel->calculatePrice($raffleId, $quantity);

        // Criar registro de compra
        $purchaseData = [
            'raffle_id' => $raffleId,
            'user_id' => $this->session->get('isLoggedIn') ? $this->session->get('id') : null,
            'buyer_name' => $buyerName,
            'buyer_email' => $buyerEmail,
            'buyer_phone' => $buyerPhone,
            'buyer_cpf' => $buyerCpf,
            'quantity' => $quantity,
            'unit_price' => $pricing['unit_price'],
            'total_amount' => $pricing['total_price'],
            'discount_applied' => $pricing['savings'],
            'payment_method' => 'pix',
            'payment_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $purchaseId = $this->purchaseModel->createPurchase($purchaseData);

        if (!$purchaseId) {
            return redirect()->back()->with('error', 'Erro ao processar compra. Tente novamente.');
        }

        // Reservar números
        $reservedNumbers = $this->numberModel->reserveRandomNumbers(
            $raffleId,
            $quantity,
            $purchaseId,
            $this->session->get('id')
        );

        if (empty($reservedNumbers)) {
            // Falha na reserva - excluir compra
            $this->purchaseModel->delete($purchaseId);
            return redirect()->back()->with('error', 'Não foi possível reservar os números. Tente novamente.');
        }

        // Salvar distribuição de campanhas
        if (!empty($campaignIds)) {
            $distributionModel = new RaffleCampaignDistributionModel();
            $campaignAmount = $pricing['total_price'] * ($raffle['campaign_percentage'] / 100);
            $distributionModel->createDistributions($purchaseId, $campaignIds, $campaignAmount);
        }

        // Gerar PIX via Mercado Pago (ou modo desenvolvimento)
        $pixData = $this->generatePixPayment($purchaseId, $pricing['total_price'], $buyerEmail, $buyerCpf, $buyerName);

        if ($pixData) {
            $this->purchaseModel->update($purchaseId, [
                'payment_id' => $pixData['payment_id'],
                'pix_code' => $pixData['pix_code'],
                'pix_qrcode' => $pixData['pix_qrcode'],
            ]);
        }

        // Redirecionar para página de pagamento
        return redirect()->to("/rifas/pagamento/{$purchaseId}");
    }

    /**
     * Página de pagamento (aguardando PIX)
     */
    public function payment(int $purchaseId)
    {
        $purchase = $this->purchaseModel->getPurchaseWithRaffle($purchaseId);

        if (!$purchase) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Verificar se é o dono da compra
        $userId = $this->session->get('id');
        if ($purchase['user_id'] && $purchase['user_id'] != $userId) {
            return redirect()->to('/rifas')->with('error', 'Acesso negado.');
        }

        // Calcular tempo restante
        $createdAt = strtotime($purchase['created_at']);
        $expiresAt = $createdAt + (30 * 60); // 30 minutos
        $remainingSeconds = max(0, $expiresAt - time());

        return view('raffles/payment', [
            'title' => 'Pagamento | DoarFazBem',
            'purchase' => $purchase,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    /**
     * Página de sucesso após pagamento
     */
    public function success(int $purchaseId)
    {
        $purchase = $this->purchaseModel->getPurchaseWithRaffle($purchaseId);

        if (!$purchase || $purchase['payment_status'] !== 'paid') {
            return redirect()->to('/rifas')->with('error', 'Compra não encontrada ou não paga.');
        }

        $numbers = $this->numberModel->getPurchaseNumbers($purchaseId);

        // Busca distribuição de campanhas
        $distributionModel = new RaffleCampaignDistributionModel();
        $distributions = $distributionModel->getPurchaseDistributions($purchaseId);

        return view('raffles/success', [
            'title' => 'Compra Confirmada | DoarFazBem',
            'purchase' => $purchase,
            'numbers' => $numbers,
            'distributions' => $distributions,
        ]);
    }

    /**
     * Meus números (usuário logado)
     */
    public function myNumbers()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Faça login para ver seus números.');
        }

        $userId = $this->session->get('id');
        $raffle = $this->raffleModel->getActiveRaffle();

        $numbers = [];
        $purchases = [];
        $ranking = null;

        if ($raffle) {
            $numbers = $this->numberModel->getUserNumbers($raffle['id'], $userId);
            $purchases = $this->purchaseModel->getUserPurchases($userId, $raffle['id']);
            $ranking = $this->rankingModel->getUserRankingData($raffle['id'], $userId);
        }

        return view('raffles/my_numbers', [
            'title' => 'Meus Números | DoarFazBem',
            'raffle' => $raffle,
            'numbers' => $numbers,
            'purchases' => $purchases,
            'ranking' => $ranking,
        ]);
    }

    /**
     * Verifica status do pagamento (AJAX)
     */
    public function checkPaymentStatus(int $purchaseId)
    {
        $purchase = $this->purchaseModel->find($purchaseId);

        if (!$purchase) {
            return $this->response->setJSON(['error' => 'Compra não encontrada']);
        }

        return $this->response->setJSON([
            'status' => $purchase['payment_status'],
            'paid' => $purchase['payment_status'] === 'paid',
            'expired' => $purchase['payment_status'] === 'expired',
        ]);
    }

    /**
     * Valida assinatura do webhook do Mercado Pago
     * Mercado Pago usa x-signature header com ts e v1
     */
    private function validateMercadoPagoSignature(string $xSignature, string $xRequestId, string $dataId): bool
    {
        $webhookSecret = env('mercadopago.webhook_secret', '');

        if (empty($webhookSecret)) {
            log_message('warning', 'MercadoPago Webhook: Secret não configurado - validação desabilitada');
            return true;
        }

        if (empty($xSignature)) {
            log_message('error', 'MercadoPago Webhook: Assinatura ausente');
            return false;
        }

        // Extrair ts e v1 do header
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $ts = $parts['ts'] ?? '';
        $hash = $parts['v1'] ?? '';

        if (empty($ts) || empty($hash)) {
            log_message('error', 'MercadoPago Webhook: ts ou v1 ausente na assinatura');
            return false;
        }

        // Criar string de validação
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";

        // Calcular HMAC esperado
        $expectedHash = hash_hmac('sha256', $manifest, $webhookSecret);

        return hash_equals($expectedHash, $hash);
    }

    /**
     * Webhook do Mercado Pago
     */
    public function webhook()
    {
        $auditLog = new AuditLogModel();

        // Validar assinatura do webhook
        $xSignature = $this->request->getHeaderLine('x-signature');
        $xRequestId = $this->request->getHeaderLine('x-request-id');
        $input = $this->request->getJSON(true);

        log_message('info', 'Raffle Webhook received: ' . json_encode($input));

        if (!$input) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid payload']);
        }

        // Obter payment_id do webhook
        $paymentId = $input['data']['id'] ?? null;

        // Validar assinatura HMAC
        if (!$this->validateMercadoPagoSignature($xSignature, $xRequestId, (string)$paymentId)) {
            $auditLog->logSuspicious('Webhook MercadoPago com assinatura inválida', null, [
                'ip' => $this->request->getIPAddress(),
                'payment_id' => $paymentId,
            ]);

            log_message('error', 'MercadoPago Webhook: Assinatura inválida - IP: ' . $this->request->getIPAddress());

            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid signature']);
        }

        if (!$paymentId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Payment ID not found']);
        }

        // Registrar no audit log
        $auditLog->log(
            AuditLogModel::ACTION_WEBHOOK_RECEIVED,
            null,
            'webhook_mercadopago',
            null,
            null,
            null,
            ['payment_id' => $paymentId, 'type' => $input['type'] ?? 'unknown']
        );

        // Buscar compra pelo payment_id
        $purchase = $this->purchaseModel->findByPaymentId($paymentId);

        if (!$purchase) {
            log_message('warning', "Raffle purchase not found for payment: {$paymentId}");
            return $this->response->setJSON(['status' => 'ignored']);
        }

        // Consultar status real no Mercado Pago
        $mpService = new \App\Libraries\MercadoPagoService();
        $paymentStatus = $mpService->getPaymentStatus($paymentId);

        if (!$paymentStatus['success']) {
            log_message('error', "Erro ao consultar pagamento: " . json_encode($paymentStatus));
            return $this->response->setJSON(['status' => 'error']);
        }

        // Processar apenas se aprovado
        if ($paymentStatus['status'] === 'approved') {
            // Confirmar pagamento
            $result = $this->numberModel->confirmPayment($purchase['id']);

            $this->purchaseModel->updatePaymentStatus(
                $purchase['id'],
                'paid',
                $result['instant_prize']
            );

            // Atualizar estatísticas da rifa
            $this->raffleModel->updateStatsAfterPurchase(
                $purchase['raffle_id'],
                $purchase['quantity'],
                $purchase['total_amount']
            );

            // Atualizar ranking
            if ($purchase['user_id']) {
                $this->rankingModel->updateRanking(
                    $purchase['raffle_id'],
                    $purchase['user_id'],
                    $purchase['buyer_name'],
                    $purchase['buyer_email'],
                    $purchase['quantity'],
                    $purchase['total_amount']
                );
            }

            // Verificar se rifa esgotou
            $this->raffleModel->checkSoldOut($purchase['raffle_id']);

            // Enviar notificação WhatsApp
            $this->sendWhatsAppNotification($purchase);

            log_message('info', "Raffle payment confirmed: Purchase #{$purchase['id']}");
        }

        return $this->response->setJSON(['status' => 'ok']);
    }

    /**
     * Gera pagamento PIX via Mercado Pago
     */
    protected function generatePixPayment(int $purchaseId, float $amount, string $email, string $cpf, string $name): ?array
    {
        $mpService = new \App\Libraries\MercadoPagoService();

        $result = $mpService->createPixPayment([
            'amount' => $amount,
            'email' => $email,
            'cpf' => $cpf,
            'name' => $name,
            'description' => "Numeros da Sorte - Compra #{$purchaseId}",
            'external_reference' => "raffle_purchase_{$purchaseId}",
        ]);

        if (!$result['success']) {
            log_message('error', 'Erro ao criar PIX: ' . json_encode($result));
            return null;
        }

        return [
            'payment_id' => $result['payment_id'],
            'pix_code' => $result['pix_code'],
            'pix_qrcode' => $result['pix_qrcode_base64'],
        ];
    }

    /**
     * Simula confirmação de pagamento (desenvolvimento)
     */
    public function simulatePayment(int $purchaseId)
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/rifas')->with('error', 'Função disponível apenas em desenvolvimento.');
        }

        $purchase = $this->purchaseModel->find($purchaseId);

        if (!$purchase || $purchase['payment_status'] !== 'pending') {
            return redirect()->to('/rifas')->with('error', 'Compra não encontrada ou já processada.');
        }

        // Confirmar números
        $result = $this->numberModel->confirmPayment($purchaseId);

        // Atualizar compra
        $this->purchaseModel->updatePaymentStatus($purchaseId, 'paid', $result['instant_prize']);

        // Atualizar estatísticas
        $this->raffleModel->updateStatsAfterPurchase(
            $purchase['raffle_id'],
            $purchase['quantity'],
            $purchase['total_amount']
        );

        // Atualizar ranking
        if ($purchase['user_id']) {
            $this->rankingModel->updateRanking(
                $purchase['raffle_id'],
                $purchase['user_id'],
                $purchase['buyer_name'],
                $purchase['buyer_email'],
                $purchase['quantity'],
                $purchase['total_amount']
            );
        }

        // Verificar se rifa esgotou
        $this->raffleModel->checkSoldOut($purchase['raffle_id']);

        // Enviar notificação WhatsApp
        $this->sendWhatsAppNotification($purchase);

        return redirect()->to("/rifas/sucesso/{$purchaseId}");
    }

    /**
     * Envia notificação WhatsApp após confirmação de pagamento
     */
    protected function sendWhatsAppNotification(array $purchase): void
    {
        try {
            $whatsApp = new \App\Libraries\WhatsAppService();

            // Se não configurado, sai silenciosamente
            if (!$whatsApp->isConfigured()) {
                log_message('info', 'WhatsApp: Serviço não configurado, pulando notificação');
                return;
            }

            // Buscar telefone do comprador
            $phone = $purchase['buyer_phone'] ?? null;

            if (!$phone && $purchase['user_id']) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($purchase['user_id']);
                $phone = $user['phone'] ?? null;
            }

            if (!$phone) {
                log_message('info', 'WhatsApp: Telefone não disponível para compra #' . $purchase['id']);
                return;
            }

            // Buscar rifa
            $raffle = $this->raffleModel->find($purchase['raffle_id']);

            // Buscar números comprados
            $numbers = $this->numberModel->getPurchaseNumbers($purchase['id']);
            $numbersList = array_column($numbers, 'number');

            // Enviar notificação
            $result = $whatsApp->sendRafflePurchaseNotification($phone, [
                'quantity' => $purchase['quantity'],
                'total' => $purchase['total_amount'],
                'numbers' => $numbersList,
                'draw_date' => $raffle['federal_lottery_date'] ?? date('Y-m-d', strtotime('+30 days')),
            ]);

            if ($result['success']) {
                log_message('info', 'WhatsApp: Notificação enviada para compra #' . $purchase['id']);
            }

            // Verificar se ganhou prêmio instantâneo
            if (!empty($purchase['instant_prize'])) {
                $prize = json_decode($purchase['instant_prize'], true);
                if ($prize) {
                    $whatsApp->sendInstantPrizeNotification($phone, [
                        'number' => $prize['number'],
                        'prize_name' => $prize['name'],
                        'prize_amount' => $prize['value'],
                    ]);
                }
            }

        } catch (\Exception $e) {
            log_message('error', 'WhatsApp Error: ' . $e->getMessage());
        }
    }

    /**
     * Historico publico de rifas finalizadas
     */
    public function history()
    {
        $db = \Config\Database::connect();
        $winnerModel = new RaffleWinnerModel();

        // Buscar rifas finalizadas
        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        $raffles = $db->table('raffles')
            ->where('status', 'finished')
            ->orderBy('federal_lottery_date', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Total para paginacao
        $total = $db->table('raffles')
            ->where('status', 'finished')
            ->countAllResults();

        // Carregar ganhadores e campanhas para cada rifa
        foreach ($raffles as &$raffle) {
            // Ganhadores (resumo - top 5)
            $raffle['winners'] = $db->table('raffle_winners')
                ->where('raffle_id', $raffle['id'])
                ->orderBy('prize_type', 'ASC')
                ->orderBy('prize_amount', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Campanhas beneficiadas
            $raffle['campaigns'] = $db->table('raffle_campaign_distributions rcd')
                ->select('rcd.*, c.title as campaign_title')
                ->join('campaigns c', 'c.id = rcd.campaign_id')
                ->where('rcd.raffle_id', $raffle['id'])
                ->get()
                ->getResultArray();
        }

        // Estatisticas gerais
        $stats = [
            'total_raffles' => $total,
            'total_winners' => $db->table('raffle_winners')->countAllResults(),
            'total_prizes' => $db->table('raffle_winners')
                ->where('payment_status', 'paid')
                ->selectSum('prize_amount')
                ->get()
                ->getRow()->prize_amount ?? 0,
            'total_campaigns' => $db->table('raffle_campaign_distributions')
                ->where('transferred', 1)
                ->selectSum('amount')
                ->get()
                ->getRow()->amount ?? 0,
        ];

        // Paginacao simples
        $pager = null;
        if ($total > $perPage) {
            $pager = \Config\Services::pager();
            $pager->setPath('rifas/historico');
            $pager->makeLinks($page, $perPage, $total);
        }

        return view('raffles/history', [
            'title' => 'Historico de Rifas | DoarFazBem',
            'raffles' => $raffles,
            'stats' => $stats,
            'pager' => $pager,
        ]);
    }

    /**
     * Detalhes de uma rifa finalizada
     */
    public function historyDetail(string $slug)
    {
        $raffle = $this->raffleModel->findBySlug($slug);

        if (!$raffle || $raffle['status'] !== 'finished') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();
        $winnerModel = new RaffleWinnerModel();

        // Ganhador principal
        $mainWinner = $db->table('raffle_winners')
            ->where('raffle_id', $raffle['id'])
            ->where('prize_type', 'main')
            ->get()
            ->getRowArray();

        // Ganhadores do ranking
        $rankingWinners = $db->table('raffle_winners')
            ->where('raffle_id', $raffle['id'])
            ->where('prize_type', 'ranking')
            ->orderBy('ranking_position', 'ASC')
            ->get()
            ->getResultArray();

        // Ganhadores de premios especiais
        $specialWinners = $db->table('raffle_winners')
            ->where('raffle_id', $raffle['id'])
            ->where('prize_type', 'special')
            ->orderBy('prize_amount', 'DESC')
            ->get()
            ->getResultArray();

        // Campanhas beneficiadas
        $campaigns = $db->table('raffle_campaign_distributions rcd')
            ->select('rcd.*, c.title as campaign_title, c.image as campaign_image')
            ->join('campaigns c', 'c.id = rcd.campaign_id')
            ->where('rcd.raffle_id', $raffle['id'])
            ->get()
            ->getResultArray();

        return view('raffles/history_detail', [
            'title' => $raffle['title'] . ' - Resultado | DoarFazBem',
            'raffle' => $raffle,
            'mainWinner' => $mainWinner,
            'rankingWinners' => $rankingWinners,
            'specialWinners' => $specialWinners,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Verifica premio por codigo de verificacao
     */
    public function verifyPrize()
    {
        $code = $this->request->getGet('code');

        if (empty($code)) {
            return redirect()->to('/rifas/historico')
                ->with('error', 'Informe o codigo de verificacao.');
        }

        $winnerModel = new RaffleWinnerModel();
        $winner = $winnerModel->getByVerificationCode(strtoupper(trim($code)));

        if (!$winner) {
            return redirect()->to('/rifas/historico')
                ->with('error', 'Codigo de verificacao invalido ou nao encontrado.');
        }

        // Buscar dados da rifa
        $raffle = $this->raffleModel->find($winner['raffle_id']);

        return view('raffles/verify_result', [
            'title' => 'Verificacao de Premio | DoarFazBem',
            'winner' => $winner,
            'raffle' => $raffle,
        ]);
    }
}
