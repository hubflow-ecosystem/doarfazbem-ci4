<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para configurações do motor SEO
 * Tabela: seo_config (já existente)
 */
class SeoConfigModel extends Model
{
  protected $table            = 'seo_config';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $useSoftDeletes   = false;
  protected $useTimestamps    = false;

  protected $allowedFields = [
    'config_key', 'config_value', 'updated_at',
  ];

  /**
   * Obter valor de configuração
   */
  public function getConfig(string $key, $default = null): ?string
  {
    $row = $this->where('config_key', $key)->first();
    return $row ? $row['config_value'] : $default;
  }

  /**
   * Definir valor de configuração
   */
  public function setConfig(string $key, string $value): bool
  {
    $existing = $this->where('config_key', $key)->first();

    if ($existing) {
      return $this->update($existing['id'], [
        'config_value' => $value,
        'updated_at'   => date('Y-m-d H:i:s'),
      ]);
    }

    return (bool) $this->insert([
      'config_key'   => $key,
      'config_value' => $value,
      'updated_at'   => date('Y-m-d H:i:s'),
    ]);
  }

  /**
   * Obter todas as configurações como array associativo
   */
  public function getAll(): array
  {
    $rows   = $this->findAll();
    $config = [];
    foreach ($rows as $row) {
      $config[$row['config_key']] = $row['config_value'];
    }
    return $config;
  }

  /**
   * Verificar se o motor está habilitado
   */
  public function isEnabled(): bool
  {
    return $this->getConfig('engine_enabled', 'false') === 'true';
  }

  /**
   * Verificar se auto-publicação está ativa
   */
  public function isAutoPublish(): bool
  {
    return $this->getConfig('auto_publish', 'false') === 'true';
  }
}
