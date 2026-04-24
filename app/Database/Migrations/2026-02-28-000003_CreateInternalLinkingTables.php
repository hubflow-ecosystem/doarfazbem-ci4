<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria tabelas para o sistema de linkagem interna automática
 * - seo_link_rules: regras keyword→URL gerenciadas pelo admin
 * - seo_link_category_map: mapeamento blog_category ↔ entidade
 */
class CreateInternalLinkingTables extends Migration
{
  public function up()
  {
    // Regras de linkagem automática (keyword → URL)
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'keyword' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'Palavra-chave a ser linkada'],
      'keyword_hash' => ['type' => 'VARCHAR', 'constraint' => 32, 'comment' => 'md5 do keyword em lowercase'],
      'target_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'comment' => 'URL de destino do link'],
      'target_label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Atributo title do link'],
      'open_new_tab' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
      'case_sensitive' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
      'match_whole_word' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'priority' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'default' => 100, 'comment' => 'Menor = maior prioridade'],
      'category_scope' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => 'Slug da categoria do blog (null = todas)'],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'hit_count' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('keyword_hash');
    $this->forge->addKey('is_active');
    $this->forge->addKey('priority');
    $this->forge->addKey('category_scope');
    $this->forge->createTable('seo_link_rules', true);

    // Mapeamento de categorias (blog ↔ entidade)
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'blog_category_slug' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'Slug da categoria do blog'],
      'entity_type' => ['type' => 'ENUM', 'constraint' => ['campaign_category', 'page', 'raffle', 'custom']],
      'entity_value' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'Valor: slug da categoria, URL, etc.'],
      'widget_label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Título do widget'],
      'widget_limit' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 3],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('blog_category_slug');
    $this->forge->addKey('is_active');
    $this->forge->createTable('seo_link_category_map', true);
  }

  public function down()
  {
    $this->forge->dropTable('seo_link_category_map', true);
    $this->forge->dropTable('seo_link_rules', true);
  }
}
