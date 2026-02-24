<?php

namespace App\Controllers;

use App\Models\DonationModel;
use App\Models\CampaignModel;
use App\Models\UserModel;

/**
 * Controller para geração de recibos de doação
 */
class ReceiptController extends BaseController
{
    protected $donationModel;
    protected $campaignModel;
    protected $userModel;

    public function __construct()
    {
        $this->donationModel = new DonationModel();
        $this->campaignModel = new CampaignModel();
        $this->userModel = new UserModel();
    }

    /**
     * Gera recibo de doação em HTML (para impressão/PDF)
     */
    public function donation(int $donationId)
    {
        // Buscar doação
        $donation = $this->donationModel->find($donationId);

        if (!$donation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Doação não encontrada');
        }

        // Verificar se o usuário pode ver este recibo
        $userId = session()->get('id');
        $userRole = session()->get('role');

        // Pode ver: o próprio doador, o criador da campanha ou admin
        $campaign = $this->campaignModel->find($donation['campaign_id']);

        $canView = false;

        // Admin sempre pode ver
        if (in_array($userRole, ['admin', 'superadmin'])) {
            $canView = true;
        }
        // Doador pode ver seu próprio recibo
        elseif ($donation['user_id'] && $donation['user_id'] == $userId) {
            $canView = true;
        }
        // Criador da campanha pode ver recibos de sua campanha
        elseif ($campaign && $campaign['user_id'] == $userId) {
            $canView = true;
        }
        // Doação anônima com email correspondente
        elseif (!$donation['user_id'] && session()->get('email') === $donation['donor_email']) {
            $canView = true;
        }

        if (!$canView) {
            return redirect()->to('/dashboard/my-donations')
                ->with('error', 'Você não tem permissão para ver este recibo.');
        }

        // Buscar dados do criador da campanha
        $creator = null;
        if ($campaign) {
            $creator = $this->userModel->find($campaign['user_id']);
        }

        // Gerar número do recibo
        $receiptNumber = $this->generateReceiptNumber($donation);

        $data = [
            'title' => 'Recibo de Doação #' . $receiptNumber,
            'donation' => $donation,
            'campaign' => $campaign,
            'creator' => $creator,
            'receiptNumber' => $receiptNumber,
            'printMode' => true,
        ];

        return view('receipts/donation', $data);
    }

    /**
     * Download do recibo como PDF (usando biblioteca TCPDF se disponível)
     * Por enquanto, redireciona para versão HTML com instruções de impressão
     */
    public function downloadPdf(int $donationId)
    {
        // Por enquanto, redireciona para a versão HTML
        // Em produção, integrar com TCPDF ou DOMPDF
        return redirect()->to("/receipt/donation/{$donationId}");
    }

    /**
     * Gera número único do recibo
     */
    private function generateReceiptNumber(array $donation): string
    {
        $year = date('Y', strtotime($donation['created_at']));
        $month = date('m', strtotime($donation['created_at']));

        return sprintf('DFB-%s%s-%06d', $year, $month, $donation['id']);
    }

    /**
     * Recibo de compra de rifa
     */
    public function raffle(int $purchaseId)
    {
        $purchaseModel = new \App\Models\RafflePurchaseModel();
        $raffleModel = new \App\Models\RaffleModel();
        $numberModel = new \App\Models\RaffleNumberModel();

        $purchase = $purchaseModel->find($purchaseId);

        if (!$purchase) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Compra não encontrada');
        }

        // Verificar permissão
        $userId = session()->get('id');
        $userRole = session()->get('role');

        $canView = false;

        if (in_array($userRole, ['admin', 'superadmin'])) {
            $canView = true;
        } elseif ($purchase['user_id'] && $purchase['user_id'] == $userId) {
            $canView = true;
        } elseif (session()->get('email') === $purchase['buyer_email']) {
            $canView = true;
        }

        if (!$canView) {
            return redirect()->to('/rifas/meus-numeros')
                ->with('error', 'Você não tem permissão para ver este recibo.');
        }

        $raffle = $raffleModel->find($purchase['raffle_id']);
        $numbers = $numberModel->getPurchaseNumbers($purchaseId);

        $receiptNumber = sprintf('RIF-%s-%06d', date('Ym', strtotime($purchase['created_at'])), $purchase['id']);

        $data = [
            'title' => 'Recibo de Compra #' . $receiptNumber,
            'purchase' => $purchase,
            'raffle' => $raffle,
            'numbers' => $numbers,
            'receiptNumber' => $receiptNumber,
            'printMode' => true,
        ];

        return view('receipts/raffle', $data);
    }
}
