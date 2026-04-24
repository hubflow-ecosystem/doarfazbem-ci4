<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Insere configurações padrão do motor SEO na tabela seo_config
 */
class SeoEngineConfigSeeder extends Seeder
{
  public function run()
  {
    $db = \Config\Database::connect();

    if (!in_array('seo_config', $db->listTables())) {
      echo "Tabela seo_config não existe.\n";
      return;
    }

    $configs = [
      ['config_key' => 'engine_enabled', 'config_value' => 'true'],
      ['config_key' => 'auto_publish', 'config_value' => 'false'],
      ['config_key' => 'min_impressions_content_gap', 'config_value' => '20'],
      ['config_key' => 'min_impressions_low_ctr', 'config_value' => '50'],
      ['config_key' => 'striking_distance_min', 'config_value' => '4'],
      ['config_key' => 'striking_distance_max', 'config_value' => '20'],
      ['config_key' => 'target_ctr', 'config_value' => '5.0'],
      ['config_key' => 'max_articles_per_day', 'config_value' => '5'],
      ['config_key' => 'max_enrichments_per_day', 'config_value' => '9999'],
      ['config_key' => 'max_top_positions_per_day', 'config_value' => '9999'],
      ['config_key' => 'last_collect_date', 'config_value' => 'Nunca'],
      ['config_key' => 'last_analyze_date', 'config_value' => 'Nunca'],
      ['config_key' => 'last_execute_date', 'config_value' => 'Nunca'],
    ];

    $inserted = 0;
    foreach ($configs as $config) {
      $existing = $db->table('seo_config')->where('config_key', $config['config_key'])->get()->getRow();
      if ($existing) continue;

      $config['updated_at'] = date('Y-m-d H:i:s');
      $db->table('seo_config')->insert($config);
      $inserted++;
    }

    echo "{$inserted} configurações do motor SEO inseridas.\n";
  }
}
