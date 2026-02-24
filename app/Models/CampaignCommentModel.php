<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignCommentModel extends Model
{
    protected $table = 'campaign_comments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'campaign_id',
        'user_id',
        'donor_name',
        'donor_email',
        'comment',
        'is_anonymous',
        'status',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'comment' => 'required|min_length[3]|max_length[1000]'
    ];

    protected $validationMessages = [
        'campaign_id' => [
            'required' => 'O ID da campanha é obrigatório'
        ],
        'comment' => [
            'required' => 'O comentário é obrigatório',
            'min_length' => 'O comentário deve ter no mínimo 3 caracteres',
            'max_length' => 'O comentário deve ter no máximo 1000 caracteres'
        ]
    ];

    /**
     * Buscar comentários aprovados de uma campanha
     */
    public function getByCampaign(int $campaignId, int $limit = null)
    {
        $builder = $this->select('campaign_comments.*, users.name as user_name')
            ->join('users', 'users.id = campaign_comments.user_id', 'left')
            ->where('campaign_id', $campaignId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        $comments = $builder->findAll();

        // Formatar nome do doador (usar anônimo ou nome real)
        foreach ($comments as &$comment) {
            if ($comment['is_anonymous']) {
                $comment['display_name'] = 'Doador Anônimo';
            } elseif ($comment['user_name']) {
                $comment['display_name'] = $comment['user_name'];
            } elseif ($comment['donor_name']) {
                $comment['display_name'] = $comment['donor_name'];
            } else {
                $comment['display_name'] = 'Apoiador';
            }
        }

        return $comments;
    }

    /**
     * Contar comentários aprovados de uma campanha
     */
    public function countByCampaign(int $campaignId): int
    {
        return $this->where('campaign_id', $campaignId)
            ->where('status', 'approved')
            ->countAllResults();
    }

    /**
     * Adicionar comentário (com ou sem usuário logado)
     */
    public function addComment(array $data): bool
    {
        return $this->insert($data) !== false;
    }

    /**
     * Buscar comentários pendentes de moderação
     */
    public function getPendingComments(int $limit = 50)
    {
        return $this->select('campaign_comments.*, campaigns.title as campaign_title')
            ->join('campaigns', 'campaigns.id = campaign_comments.campaign_id')
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Aprovar comentário
     */
    public function approveComment(int $commentId): bool
    {
        return $this->update($commentId, ['status' => 'approved']);
    }

    /**
     * Rejeitar comentário
     */
    public function rejectComment(int $commentId): bool
    {
        return $this->update($commentId, ['status' => 'rejected']);
    }
}
