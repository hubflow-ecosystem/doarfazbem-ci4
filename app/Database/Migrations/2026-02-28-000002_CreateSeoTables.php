<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria tabelas de SEO: page_meta, redirects, 404_log, faqs, config
 */
class CreateSeoTables extends Migration
{
  public function up()
  {
    // Meta tags personalizadas por página
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'page_type' => ['type' => 'VARCHAR', 'constraint' => 50],
      'page_identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
      'canonical_url' => ['type' => 'VARCHAR', 'constraint' => 500],
      'meta_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'meta_description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'meta_keywords' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'robots' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
      'og_image' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'schema_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
      'custom_schema' => ['type' => 'JSON', 'null' => true],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('canonical_url');
    $this->forge->addKey(['page_type', 'page_identifier']);
    $this->forge->createTable('seo_page_meta', true);

    // Redirects SEO (301/302)
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'source_url' => ['type' => 'VARCHAR', 'constraint' => 500],
      'source_url_hash' => ['type' => 'VARCHAR', 'constraint' => 32],
      'destination_url' => ['type' => 'VARCHAR', 'constraint' => 500],
      'redirect_type' => ['type' => 'SMALLINT', 'default' => 301],
      'match_type' => ['type' => 'ENUM', 'constraint' => ['exact', 'prefix', 'regex'], 'default' => 'exact'],
      'priority' => ['type' => 'INT', 'default' => 100],
      'hit_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'last_hit_at' => ['type' => 'DATETIME', 'null' => true],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'notes' => ['type' => 'TEXT', 'null' => true],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('source_url_hash');
    $this->forge->addKey('is_active');
    $this->forge->createTable('seo_redirects', true);

    // Monitor de 404
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'url' => ['type' => 'VARCHAR', 'constraint' => 500],
      'url_hash' => ['type' => 'VARCHAR', 'constraint' => 32],
      'referrer' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
      'hit_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
      'first_seen_at' => ['type' => 'DATETIME', 'null' => true],
      'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
      'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'ignored', 'redirected'], 'default' => 'pending'],
      'redirect_to' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'notes' => ['type' => 'TEXT', 'null' => true],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('url_hash');
    $this->forge->addKey('status');
    $this->forge->addKey('hit_count');
    $this->forge->createTable('seo_404_log', true);

    // FAQs para Schema.org FAQPage
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'page_type' => ['type' => 'VARCHAR', 'constraint' => 50],
      'page_identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
      'question' => ['type' => 'TEXT'],
      'answer' => ['type' => 'TEXT'],
      'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey(['page_type', 'page_identifier']);
    $this->forge->createTable('seo_faqs', true);

    // Configurações de SEO
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'config_key' => ['type' => 'VARCHAR', 'constraint' => 100],
      'config_value' => ['type' => 'TEXT', 'null' => true],
      'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('config_key');
    $this->forge->createTable('seo_config', true);
  }

  public function down()
  {
    $this->forge->dropTable('seo_config', true);
    $this->forge->dropTable('seo_faqs', true);
    $this->forge->dropTable('seo_404_log', true);
    $this->forge->dropTable('seo_redirects', true);
    $this->forge->dropTable('seo_page_meta', true);
  }
}
