<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder de regras padrão de linkagem interna
 *
 * Cria regras keyword→URL para auto-linking nos artigos do blog,
 * conectando automaticamente o conteúdo às páginas relevantes.
 */
class DefaultLinkRulesSeeder extends Seeder
{
  public function run()
  {
    $rules = [
      // Campanhas por categoria
      [
        'keyword' => 'campanha de doação',
        'target_url' => '/campaigns',
        'target_label' => 'Ver campanhas ativas',
        'priority' => 1,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'campanha médica',
        'target_url' => '/campaigns?category=medica',
        'target_label' => 'Campanhas médicas',
        'priority' => 2,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'tratamento médico',
        'target_url' => '/campaigns?category=medica',
        'target_label' => 'Campanhas para tratamento',
        'priority' => 3,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'projeto social',
        'target_url' => '/campaigns?category=social',
        'target_label' => 'Projetos sociais',
        'priority' => 2,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'educação',
        'target_url' => '/campaigns?category=educacao',
        'target_label' => 'Campanhas de educação',
        'priority' => 5,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],

      // Rifas
      [
        'keyword' => 'rifa solidária',
        'target_url' => '/rifas',
        'target_label' => 'Rifas ativas',
        'priority' => 2,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'números da sorte',
        'target_url' => '/rifas',
        'target_label' => 'Participar da rifa',
        'priority' => 3,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],

      // Páginas institucionais
      [
        'keyword' => 'doação',
        'target_url' => '/campaigns',
        'target_label' => 'Fazer uma doação',
        'priority' => 10,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'criar campanha',
        'target_url' => '/campaigns/create',
        'target_label' => 'Criar sua campanha',
        'priority' => 3,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'como funciona',
        'target_url' => '/como-funciona',
        'target_label' => 'Saiba como funciona',
        'priority' => 5,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'transparência',
        'target_url' => '/sobre',
        'target_label' => 'Sobre o DoarFazBem',
        'priority' => 8,
        'category_scope' => 'transparencia',
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'crowdfunding',
        'target_url' => '/como-funciona',
        'target_label' => 'Entenda o crowdfunding',
        'priority' => 6,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
      [
        'keyword' => 'DoarFazBem',
        'target_url' => '/sobre',
        'target_label' => 'Conheça o DoarFazBem',
        'priority' => 15,
        'category_scope' => null,
        'match_whole_word' => 1,
        'case_sensitive' => 1,
      ],
      [
        'keyword' => 'PIX',
        'target_url' => '/como-funciona',
        'target_label' => 'Doe via PIX',
        'priority' => 12,
        'category_scope' => null,
        'match_whole_word' => 1,
        'case_sensitive' => 1,
      ],
      [
        'keyword' => 'impacto social',
        'target_url' => '/blog/categoria/impacto-social',
        'target_label' => 'Artigos sobre impacto social',
        'priority' => 4,
        'category_scope' => null,
        'match_whole_word' => 1,
      ],
    ];

    $db = \Config\Database::connect();

    // Verificar se tabela existe
    if (!in_array('seo_link_rules', $db->listTables())) {
      echo "Tabela seo_link_rules não existe. Execute a migration primeiro.\n";
      return;
    }

    $inserted = 0;
    foreach ($rules as $rule) {
      $keywordHash = md5(strtolower($rule['keyword']));

      // Verificar se já existe
      $existing = $db->table('seo_link_rules')
        ->where('keyword_hash', $keywordHash)
        ->get()->getRow();

      if ($existing) continue;

      $db->table('seo_link_rules')->insert([
        'keyword' => $rule['keyword'],
        'keyword_hash' => $keywordHash,
        'target_url' => $rule['target_url'],
        'target_label' => $rule['target_label'],
        'open_new_tab' => 0,
        'case_sensitive' => $rule['case_sensitive'] ?? 0,
        'match_whole_word' => $rule['match_whole_word'],
        'priority' => $rule['priority'],
        'category_scope' => $rule['category_scope'],
        'is_active' => 1,
        'hit_count' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
      ]);
      $inserted++;
    }

    echo "{$inserted} regras de linkagem interna inseridas com sucesso.\n";
  }
}
