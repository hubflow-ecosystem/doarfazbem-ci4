<?php

namespace App\Controllers;

use App\Models\CampaignModel;
use App\Models\CampaignUpdateModel;
use App\Models\CampaignCommentModel;

class CampaignInteractionController extends BaseController
{
    protected $campaignModel;
    protected $updateModel;
    protected $commentModel;
    protected $session;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->updateModel = new CampaignUpdateModel();
        $this->commentModel = new CampaignCommentModel();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    // ============================================
    // ATUALIZAÇÕES DE CAMPANHA
    // ============================================

    /**
     * Criar atualização para uma campanha
     * POST /campaigns/{id}/update
     */
    public function createUpdate($campaignId)
    {
        // Verificar autenticação
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login')
                ->with('error', 'Você precisa estar logado para postar atualizações.');
        }

        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign) {
            return redirect()->back()
                ->with('error', 'Campanha não encontrada.');
        }

        // Verificar se é o criador da campanha
        if ($campaign['user_id'] != $this->session->get('id')) {
            return redirect()->back()
                ->with('error', 'Apenas o criador da campanha pode postar atualizações.');
        }

        // Validação
        if (!$this->validate([
            'title' => 'required|min_length[5]|max_length[255]',
            'content' => 'required|min_length[10]',
            'image' => 'permit_empty|uploaded[image]|max_size[image,2048]|is_image[image]'
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Upload de imagem (opcional)
        $image = $this->request->getFile('image');
        $imageName = null;

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $image->move(WRITEPATH . '../public/uploads/updates', $imageName);
        }

        // Criar atualização
        $data = [
            'campaign_id' => $campaignId,
            'user_id' => $this->session->get('id'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'image' => $imageName
        ];

        $updateId = $this->updateModel->insert($data);

        if ($updateId) {
            // Adicionar notificações à fila para todos os doadores inscritos
            $this->enqueueNotifications($updateId, $campaignId);

            return redirect()->to("/campaigns/{$campaign['slug']}")
                ->with('success', 'Atualização publicada com sucesso! Notificações serão enviadas aos apoiadores.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Erro ao publicar atualização. Tente novamente.');
    }

    /**
     * Editar atualização
     * POST /campaigns/update/{updateId}/edit
     */
    public function editUpdate($updateId)
    {
        // Verificar autenticação
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $update = $this->updateModel->find($updateId);

        if (!$update) {
            return redirect()->back()
                ->with('error', 'Atualização não encontrada.');
        }

        // Verificar se é o autor
        if ($update['user_id'] != $this->session->get('id')) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para editar esta atualização.');
        }

        // Validação
        if (!$this->validate([
            'title' => 'required|min_length[5]|max_length[255]',
            'content' => 'required|min_length[10]'
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Atualizar
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content')
        ];

        if ($this->updateModel->update($updateId, $data)) {
            $campaign = $this->campaignModel->find($update['campaign_id']);
            return redirect()->to("/campaigns/{$campaign['slug']}")
                ->with('success', 'Atualização editada com sucesso!');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Erro ao editar atualização.');
    }

    /**
     * Deletar atualização
     * POST /campaigns/update/{updateId}/delete
     */
    public function deleteUpdate($updateId)
    {
        // Verificar autenticação
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $update = $this->updateModel->find($updateId);

        if (!$update) {
            return redirect()->back()
                ->with('error', 'Atualização não encontrada.');
        }

        // Verificar se é o autor
        if ($update['user_id'] != $this->session->get('id')) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para deletar esta atualização.');
        }

        // Deletar imagem se existir
        if ($update['image']) {
            $imagePath = WRITEPATH . '../public/uploads/updates/' . $update['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($this->updateModel->delete($updateId)) {
            return redirect()->back()
                ->with('success', 'Atualização deletada com sucesso!');
        }

        return redirect()->back()
            ->with('error', 'Erro ao deletar atualização.');
    }

    // ============================================
    // COMENTÁRIOS
    // ============================================

    /**
     * Adicionar comentário em uma campanha
     * POST /campaigns/{id}/comment
     */
    public function addComment($campaignId)
    {
        $campaign = $this->campaignModel->find($campaignId);

        if (!$campaign) {
            return redirect()->back()
                ->with('error', 'Campanha não encontrada.');
        }

        // Validação
        if (!$this->validate([
            'comment' => 'required|min_length[3]|max_length[1000]',
            'donor_name' => 'permit_empty|min_length[3]|max_length[100]',
            'donor_email' => 'permit_empty|valid_email'
        ])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $isAnonymous = $this->request->getPost('is_anonymous') ? 1 : 0;
        $isLoggedIn = $this->session->get('isLoggedIn');

        // Preparar dados
        $data = [
            'campaign_id' => $campaignId,
            'comment' => $this->request->getPost('comment'),
            'is_anonymous' => $isAnonymous,
            'status' => 'approved' // Auto-aprovar (ou 'pending' se quiser moderação)
        ];

        // Se estiver logado
        if ($isLoggedIn) {
            $data['user_id'] = $this->session->get('id');
        } else {
            // Comentário de visitante
            $data['donor_name'] = $this->request->getPost('donor_name') ?: 'Apoiador';
            $data['donor_email'] = $this->request->getPost('donor_email');
        }

        if ($this->commentModel->insert($data)) {
            return redirect()->to("/campaigns/{$campaign['slug']}#comments")
                ->with('success', 'Comentário enviado com sucesso!');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Erro ao enviar comentário. Tente novamente.');
    }

    /**
     * Deletar comentário (apenas autor ou criador da campanha)
     * POST /campaigns/comment/{commentId}/delete
     */
    public function deleteComment($commentId)
    {
        // Verificar autenticação
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $comment = $this->commentModel->find($commentId);

        if (!$comment) {
            return redirect()->back()
                ->with('error', 'Comentário não encontrado.');
        }

        $campaign = $this->campaignModel->find($comment['campaign_id']);

        // Verificar se é o autor do comentário ou criador da campanha
        $canDelete = ($comment['user_id'] == $this->session->get('id')) ||
                     ($campaign['user_id'] == $this->session->get('id')) ||
                     ($this->session->get('role') == 'admin');

        if (!$canDelete) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para deletar este comentário.');
        }

        if ($this->commentModel->delete($commentId)) {
            return redirect()->back()
                ->with('success', 'Comentário deletado com sucesso!');
        }

        return redirect()->back()
            ->with('error', 'Erro ao deletar comentário.');
    }

    /**
     * Reportar comentário inadequado
     * POST /campaigns/comment/{commentId}/report
     */
    public function reportComment($commentId)
    {
        $comment = $this->commentModel->find($commentId);

        if (!$comment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Comentário não encontrado'
            ]);
        }

        // TODO: Implementar sistema de reports
        // Por enquanto, apenas mudar status para pending
        $this->commentModel->update($commentId, ['status' => 'pending']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Comentário reportado. Nossa equipe irá analisar.'
        ]);
    }

    /**
     * Adicionar notificações à fila quando atualização é publicada
     */
    protected function enqueueNotifications($updateId, $campaignId)
    {
        try {
            $preferenceModel = new \App\Models\NotificationPreference();
            $queueModel = new \App\Models\NotificationQueue();

            // Buscar todos os doadores inscritos nesta campanha
            $preferences = $preferenceModel->getByCampaign($campaignId, true);

            if (empty($preferences)) {
                log_message('info', "Nenhum doador inscrito para notificações da campanha {$campaignId}");
                return;
            }

            $emailCount = 0;
            $pushCount = 0;

            foreach ($preferences as $pref) {
                // Adicionar notificação por email
                if ($pref['notify_email']) {
                    $queueModel->enqueue($updateId, [
                        'email' => $pref['donor_email'],
                        'name' => 'Apoiador',
                    ], 'email');
                    $emailCount++;
                }

                // Adicionar notificação push
                if ($pref['notify_push'] && !empty($pref['push_token'])) {
                    $queueModel->enqueue($updateId, [
                        'email' => $pref['donor_email'],
                        'name' => 'Apoiador',
                    ], 'push', $pref['push_token']);
                    $pushCount++;
                }
            }

            log_message('info', "Notificações enfileiradas para atualização {$updateId}: {$emailCount} emails, {$pushCount} push");

        } catch (\Exception $e) {
            log_message('error', 'Erro ao enfileirar notificações: ' . $e->getMessage());
        }
    }
}
