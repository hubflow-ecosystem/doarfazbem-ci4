<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Notification Queue Model
 * Gerencia fila de notificações a serem enviadas
 */
class NotificationQueue extends Model
{
    protected $table = 'notification_queue';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'campaign_update_id',
        'recipient_email',
        'recipient_name',
        'notification_type',
        'push_token',
        'status',
        'attempts',
        'error_message',
        'sent_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Adicionar notificação à fila
     */
    public function enqueue($updateId, $recipient, $type = 'email', $pushToken = null)
    {
        return $this->insert([
            'campaign_update_id' => $updateId,
            'recipient_email' => $recipient['email'],
            'recipient_name' => $recipient['name'] ?? 'Doador',
            'notification_type' => $type,
            'push_token' => $pushToken,
            'status' => 'pending',
            'attempts' => 0,
        ]);
    }

    /**
     * Buscar notificações pendentes
     */
    public function getPending($limit = 50)
    {
        return $this->where('status', 'pending')
                    ->where('attempts <', 3) // Máximo 3 tentativas
                    ->orderBy('created_at', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Marcar como enviada
     */
    public function markAsSent($id)
    {
        return $this->update($id, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Marcar como falha
     */
    public function markAsFailed($id, $errorMessage = null)
    {
        $notification = $this->find($id);
        $attempts = ($notification['attempts'] ?? 0) + 1;

        return $this->update($id, [
            'status' => $attempts >= 3 ? 'failed' : 'pending',
            'attempts' => $attempts,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Limpar notificações antigas (> 30 dias)
     */
    public function cleanup()
    {
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

        return $this->where('sent_at <', $thirtyDaysAgo)
                    ->where('status', 'sent')
                    ->delete();
    }
}
