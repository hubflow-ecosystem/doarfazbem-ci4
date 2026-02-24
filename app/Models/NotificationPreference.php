<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Notification Preference Model
 * Gerencia preferências de notificações dos doadores
 */
class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'donor_email',
        'campaign_id',
        'notify_email',
        'notify_push',
        'push_token',
        'unsubscribe_token',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'donor_email' => 'required|valid_email|max_length[255]',
        'campaign_id' => 'required|integer',
        'notify_email' => 'in_list[0,1]',
        'notify_push' => 'in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Criar ou atualizar preferência de notificação
     */
    public function createOrUpdate($data)
    {
        // Buscar preferência existente
        $existing = $this->where('donor_email', $data['donor_email'])
                         ->where('campaign_id', $data['campaign_id'])
                         ->first();

        // Gerar token de unsubscribe se não existir
        if (!isset($data['unsubscribe_token'])) {
            $data['unsubscribe_token'] = bin2hex(random_bytes(32));
        }

        if ($existing) {
            // Atualizar
            return $this->update($existing['id'], $data);
        } else {
            // Criar novo
            return $this->insert($data);
        }
    }

    /**
     * Buscar preferências de uma campanha
     */
    public function getByCampaign($campaignId, $onlyActive = true)
    {
        $builder = $this->where('campaign_id', $campaignId);

        if ($onlyActive) {
            $builder->groupStart()
                    ->where('notify_email', 1)
                    ->orWhere('notify_push', 1)
                    ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * Buscar preferências de um doador em uma campanha
     */
    public function getByDonorAndCampaign($donorEmail, $campaignId)
    {
        return $this->where('donor_email', $donorEmail)
                    ->where('campaign_id', $campaignId)
                    ->first();
    }

    /**
     * Buscar por token de unsubscribe
     */
    public function getByUnsubscribeToken($token)
    {
        return $this->where('unsubscribe_token', $token)->first();
    }

    /**
     * Desabilitar todas as notificações (unsubscribe completo)
     */
    public function unsubscribeAll($token)
    {
        $preference = $this->getByUnsubscribeToken($token);

        if (!$preference) {
            return false;
        }

        return $this->update($preference['id'], [
            'notify_email' => 0,
            'notify_push' => 0,
        ]);
    }

    /**
     * Desabilitar apenas emails
     */
    public function unsubscribeEmail($token)
    {
        $preference = $this->getByUnsubscribeToken($token);

        if (!$preference) {
            return false;
        }

        return $this->update($preference['id'], [
            'notify_email' => 0,
        ]);
    }

    /**
     * Contar doadores inscritos em uma campanha
     */
    public function countSubscribers($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
                    ->groupStart()
                    ->where('notify_email', 1)
                    ->orWhere('notify_push', 1)
                    ->groupEnd()
                    ->countAllResults();
    }
}
