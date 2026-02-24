<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignUpdateModel extends Model
{
    protected $table = 'campaign_updates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'campaign_id',
        'user_id',
        'title',
        'content',
        'image',
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
        'user_id' => 'required|integer',
        'title' => 'required|min_length[5]|max_length[255]',
        'content' => 'required|min_length[10]'
    ];

    protected $validationMessages = [
        'campaign_id' => [
            'required' => 'O ID da campanha é obrigatório'
        ],
        'title' => [
            'required' => 'O título é obrigatório',
            'min_length' => 'O título deve ter no mínimo 5 caracteres'
        ],
        'content' => [
            'required' => 'O conteúdo é obrigatório',
            'min_length' => 'O conteúdo deve ter no mínimo 10 caracteres'
        ]
    ];

    /**
     * Buscar atualizações de uma campanha
     */
    public function getByCampaign(int $campaignId, int $limit = null)
    {
        $builder = $this->select('campaign_updates.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = campaign_updates.user_id')
            ->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Contar atualizações de uma campanha
     */
    public function countByCampaign(int $campaignId): int
    {
        return $this->where('campaign_id', $campaignId)->countAllResults();
    }

    /**
     * Buscar última atualização de uma campanha
     */
    public function getLatestUpdate(int $campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
