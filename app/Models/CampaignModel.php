<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CampaignModel
 *
 * Model para gerenciar campanhas de crowdfunding
 */
class CampaignModel extends Model
{
    protected $table            = 'campaigns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'title',
        'slug',
        'description',
        'highlights',
        'category',
        'goal_amount',
        'current_amount',
        'end_date',
        'image',
        'video_url',
        'status',
        'rejection_reason',
        'is_urgent',
        'is_featured',
        'views_count',
        'donors_count'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Regras de Validação
    protected $validationRules = [
        'title' => [
            'rules'  => 'required|min_length[10]|max_length[255]',
            'errors' => [
                'required'   => 'O título é obrigatório.',
                'min_length' => 'O título deve ter pelo menos 10 caracteres.',
                'max_length' => 'O título não pode ter mais de 255 caracteres.'
            ]
        ],
        'description' => [
            'rules'  => 'required|min_length[50]',
            'errors' => [
                'required'   => 'A descrição é obrigatória.',
                'min_length' => 'A descrição deve ter pelo menos 50 caracteres.'
            ]
        ],
        'category' => [
            'rules'  => 'required|in_list[medica,social,criativa,negocio,educacao]',
            'errors' => [
                'required' => 'A categoria é obrigatória.',
                'in_list'  => 'Categoria inválida.'
            ]
        ],
        'goal_amount' => [
            'rules'  => 'required|decimal|greater_than[0]',
            'errors' => [
                'required'     => 'A meta de arrecadação é obrigatória.',
                'decimal'      => 'Valor inválido.',
                'greater_than' => 'A meta deve ser maior que zero.'
            ]
        ],
        'end_date' => [
            'rules'  => 'required|valid_date',
            'errors' => [
                'required'   => 'A data final é obrigatória.',
                'valid_date' => 'Data inválida.'
            ]
        ]
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug'];
    protected $beforeUpdate   = ['generateSlug'];
    protected $afterInsert    = ['notifyAdminNewCampaign'];

    /**
     * Gera slug único baseado no título
     */
    protected function generateSlug(array $data)
    {
        if (!isset($data['data']['title'])) {
            return $data;
        }

        $slug = url_title($data['data']['title'], '-', true);

        // Verificar se já existe
        $count = $this->where('slug', $slug)->countAllResults();

        if ($count > 0) {
            $slug = $slug . '-' . time();
        }

        $data['data']['slug'] = $slug;

        return $data;
    }

    /**
     * Busca campanhas ativas (campanha da plataforma sempre em primeiro)
     */
    public function getActiveCampaigns($limit = 12, $offset = 0)
    {
        $campaigns = $this->where('status', 'active')
            ->where('end_date >=', date('Y-m-d'))
            ->orderBy('(CASE WHEN slug = "mantenha-a-plataforma-ativa" THEN 0 ELSE 1 END)', 'ASC', false)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);

        return $campaigns;
    }

    /**
     * Busca campanhas por categoria (campanha da plataforma sempre em primeiro)
     */
    public function getCampaignsByCategory($category, $limit = 12, $offset = 0)
    {
        return $this->where('status', 'active')
            ->where('category', $category)
            ->where('end_date >=', date('Y-m-d'))
            ->orderBy('(CASE WHEN slug = "mantenha-a-plataforma-ativa" THEN 0 ELSE 1 END)', 'ASC', false)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Busca campanhas urgentes
     */
    public function getUrgentCampaigns($limit = 6)
    {
        return $this->where('status', 'active')
            ->where('is_urgent', true)
            ->where('end_date >=', date('Y-m-d'))
            ->orderBy('end_date', 'ASC')
            ->findAll($limit);
    }

    /**
     * Busca campanhas em destaque
     */
    public function getFeaturedCampaigns($limit = 6)
    {
        return $this->where('status', 'active')
            ->where('is_featured', true)
            ->where('end_date >=', date('Y-m-d'))
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Busca campanha por slug
     */
    public function getCampaignBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Busca campanhas de um usuário
     */
    public function getUserCampaigns($userId)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Incrementa visualizações
     */
    public function incrementViews($campaignId)
    {
        $this->set('views_count', 'views_count+1', false)
            ->where('id', $campaignId)
            ->update();
    }

    /**
     * Atualiza valor arrecadado e contador de doadores
     */
    public function updateDonationStats($campaignId, $amount)
    {
        $campaign = $this->find($campaignId);

        if (!$campaign) {
            return false;
        }

        $newAmount = $campaign['current_amount'] + $amount;
        $newDonorsCount = $campaign['donors_count'] + 1;

        return $this->update($campaignId, [
            'current_amount' => $newAmount,
            'donors_count' => $newDonorsCount
        ]);
    }

    /**
     * Verifica se campanha atingiu a meta
     */
    public function hasReachedGoal($campaignId)
    {
        $campaign = $this->find($campaignId);

        if (!$campaign) {
            return false;
        }

        return $campaign['current_amount'] >= $campaign['goal_amount'];
    }

    /**
     * Calcula percentual arrecadado
     */
    public function getPercentage($campaignId)
    {
        $campaign = $this->find($campaignId);

        if (!$campaign || $campaign['goal_amount'] == 0) {
            return 0;
        }

        $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;

        return min($percentage, 100); // Máximo 100%
    }

    /**
     * Busca campanhas expiradas que não atingiram a meta
     */
    public function getFailedCampaigns()
    {
        return $this->where('status', 'active')
            ->where('end_date <', date('Y-m-d'))
            ->findAll();
    }

    /**
     * Busca campanhas para análise estatística
     */
    public function getCampaignStats()
    {
        $builder = $this->db->table($this->table);

        return $builder->select('
            COUNT(*) as total_campaigns,
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_campaigns,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_campaigns,
            SUM(current_amount) as total_raised,
            AVG(current_amount) as avg_raised,
            category
        ')
        ->groupBy('category')
        ->get()
        ->getResultArray();
    }

    /**
     * Pesquisa campanhas por termo
     */
    public function searchCampaigns($term, $limit = 20)
    {
        return $this->where('status', 'active')
            ->groupStart()
                ->like('title', $term)
                ->orLike('description', $term)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Recalcula estatísticas da campanha baseado nas doações reais
     * MÉTODO CENTRALIZADO - usar este para garantir consistência
     */
    public function recalculateStats($campaignId)
    {
        $db = \Config\Database::connect();

        // Buscar estatísticas das doações
        $result = $db->table('donations')
            ->select("
                COUNT(*) as total_donations,
                SUM(amount) as total_amount
            ", false)
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        // Atualizar campanha
        // donors_count agora representa o total de doações (cada doação = 1 doador)
        return $this->update($campaignId, [
            'current_amount' => $result['total_amount'] ?? 0,
            'donors_count' => $result['total_donations'] ?? 0
        ]);
    }

    /**
     * Busca campanha com estatísticas calculadas em tempo real
     * MÉTODO CENTRALIZADO - usar para exibição
     */
    public function getCampaignWithStats($campaignId)
    {
        $campaign = $this->find($campaignId);

        if (!$campaign) {
            return null;
        }

        $db = \Config\Database::connect();

        // Buscar estatísticas em tempo real
        $stats = $db->table('donations')
            ->select("
                COUNT(*) as total_donations,
                SUM(amount) as total_amount
            ", false)
            ->where('campaign_id', $campaignId)
            ->where('status', 'received')
            ->get()
            ->getRowArray();

        // Adicionar estatísticas calculadas
        // donors_count agora representa o número total de doações (cada doação = 1 doador)
        $campaign['current_amount'] = $stats['total_amount'] ?? 0;
        $campaign['donors_count'] = $stats['total_donations'] ?? 0;
        $campaign['total_donations'] = $stats['total_donations'] ?? 0;

        // Calcular percentual
        if ($campaign['goal_amount'] > 0) {
            $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
            $campaign['percentage'] = min($percentage, 100);
        } else {
            $campaign['percentage'] = 0;
        }

        // Calcular dias restantes
        $campaign['days_left'] = max(0, (strtotime($campaign['end_date']) - time()) / 86400);

        return $campaign;
    }

    /**
     * Callback: Notificar administradores ao criar nova campanha
     */
    protected function notifyAdminNewCampaign(array $data): array
    {
        // Só notificar se a inserção foi bem-sucedida
        if (!isset($data['id'])) {
            return $data;
        }

        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyAdminNewCampaign($data['id']);
        } catch (\Exception $e) {
            // Não bloquear a criação da campanha se notificação falhar
            log_message('error', 'Erro ao notificar admin sobre nova campanha: ' . $e->getMessage());
        }

        return $data;
    }
}
