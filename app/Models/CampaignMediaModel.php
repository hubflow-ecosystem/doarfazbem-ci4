<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignMediaModel extends Model
{
    protected $table            = 'campaign_media';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'campaign_id',
        'type',
        'url',
        'thumbnail',
        'title',
        'sort_order',
        'is_primary'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Busca mídia de uma campanha
     */
    public function getMediaByCampaign($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Busca imagens de uma campanha
     */
    public function getImagesByCampaign($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('type', 'image')
            ->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Busca vídeos de uma campanha
     */
    public function getVideosByCampaign($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('type', 'video')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Busca mídia principal
     */
    public function getPrimaryMedia($campaignId)
    {
        return $this->where('campaign_id', $campaignId)
            ->where('is_primary', 1)
            ->first();
    }

    /**
     * Define mídia como principal
     */
    public function setPrimary($mediaId, $campaignId)
    {
        // Remove primary de todas
        $this->where('campaign_id', $campaignId)
            ->set('is_primary', 0)
            ->update();

        // Define a nova primary
        return $this->update($mediaId, ['is_primary' => 1]);
    }

    /**
     * Adiciona imagem à campanha
     */
    public function addImage($campaignId, $url, $title = null, $isPrimary = false)
    {
        $maxOrder = $this->selectMax('sort_order')
            ->where('campaign_id', $campaignId)
            ->first();

        return $this->insert([
            'campaign_id' => $campaignId,
            'type' => 'image',
            'url' => $url,
            'title' => $title,
            'sort_order' => ($maxOrder['sort_order'] ?? 0) + 1,
            'is_primary' => $isPrimary ? 1 : 0
        ]);
    }

    /**
     * Adiciona vídeo à campanha
     */
    public function addVideo($campaignId, $url, $thumbnail = null, $title = null)
    {
        $maxOrder = $this->selectMax('sort_order')
            ->where('campaign_id', $campaignId)
            ->first();

        return $this->insert([
            'campaign_id' => $campaignId,
            'type' => 'video',
            'url' => $url,
            'thumbnail' => $thumbnail,
            'title' => $title,
            'sort_order' => ($maxOrder['sort_order'] ?? 0) + 1,
            'is_primary' => 0
        ]);
    }

    /**
     * Extrai thumbnail do YouTube
     */
    public function getYouTubeThumbnail($url)
    {
        $videoId = null;

        // youtube.com/watch?v=ID
        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // youtu.be/ID
        elseif (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }
        // youtube.com/embed/ID
        elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if ($videoId) {
            return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        }

        return null;
    }
}
