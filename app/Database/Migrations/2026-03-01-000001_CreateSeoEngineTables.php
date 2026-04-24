<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria tabelas do motor SEO autônomo
 * - seo_queries: dados do Google Search Console e Bing
 * - seo_opportunities: oportunidades de SEO detectadas
 * - seo_action_log: histórico de ações executadas
 * - seo_bing_analytics: analytics do Bing Webmaster Tools
 */
class CreateSeoEngineTables extends Migration
{
  public function up()
  {
    // ========================================
    // Tabela: seo_queries (dados do GSC/Bing)
    // ========================================
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'query' => ['type' => 'VARCHAR', 'constraint' => 500, 'comment' => 'Termo de busca'],
      'page_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'comment' => 'URL da página'],
      'clicks' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'impressions' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'ctr' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'comment' => 'Click-through rate %'],
      'position' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0, 'comment' => 'Posição média'],
      'device' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'desktop'],
      'country' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'BRA'],
      'source' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'google', 'comment' => 'google ou bing'],
      'date' => ['type' => 'DATE', 'comment' => 'Data dos dados'],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('date');
    $this->forge->addKey('impressions');
    $this->forge->addKey('position');
    $this->forge->addKey('source');
    $this->forge->createTable('seo_queries', true);

    // Unique key via raw SQL (CI4 não suporta bem unique composto com limit)
    $this->db->query('CREATE UNIQUE INDEX uk_query_page_date ON seo_queries (query(100), page_url(100), date, source)');
    $this->db->query('CREATE INDEX idx_query ON seo_queries (query(100))');
    $this->db->query('CREATE INDEX idx_page_url ON seo_queries (page_url(100))');

    // ========================================
    // Tabela: seo_opportunities
    // ========================================
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'type' => ['type' => 'VARCHAR', 'constraint' => 30, 'comment' => 'content_gap, low_ctr, striking_distance, top_position, enrichment'],
      'target_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'blog', 'comment' => 'blog, campaign, raffle, page, meta_only'],
      'keyword' => ['type' => 'VARCHAR', 'constraint' => 500],
      'related_keywords' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
      'current_page_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'impressions' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'clicks' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'current_ctr' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
      'current_position' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
      'potential_clicks' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'priority' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'medium', 'comment' => 'critical, high, medium, low'],
      'priority_score' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'comment' => '0-1000'],
      'ai_analysis' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
      'ai_suggested_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'ai_suggested_meta' => ['type' => 'TEXT', 'null' => true],
      'ai_suggested_content' => ['type' => 'LONGTEXT', 'null' => true],
      'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending', 'comment' => 'pending, in_progress, monitoring, failed, dismissed'],
      'executed_at' => ['type' => 'DATETIME', 'null' => true],
      'result_action' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
      'result_target_id' => ['type' => 'INT', 'null' => true],
      'result_target_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('status');
    $this->forge->addKey('type');
    $this->forge->addKey('priority_score');
    $this->forge->addKey('target_type');
    $this->forge->createTable('seo_opportunities', true);

    $this->db->query('CREATE INDEX idx_opp_keyword ON seo_opportunities (keyword(100))');

    // ========================================
    // Tabela: seo_action_log
    // ========================================
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'opportunity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
      'action_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'meta_update, create_content, enrich_content, indexing_request'],
      'target_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
      'target_id' => ['type' => 'INT', 'null' => true],
      'target_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'keyword' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'before_state' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
      'after_state' => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON'],
      'ai_model' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
      'ai_tokens_used' => ['type' => 'INT', 'default' => 0],
      'ai_cost_usd' => ['type' => 'DECIMAL', 'constraint' => '8,6', 'default' => 0],
      'success' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'error_message' => ['type' => 'TEXT', 'null' => true],
      'executed_at' => ['type' => 'DATETIME'],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('opportunity_id');
    $this->forge->addKey('executed_at');
    $this->forge->addKey('action_type');
    $this->forge->addKey('success');
    $this->forge->createTable('seo_action_log', true);

    // ========================================
    // Tabela: seo_bing_analytics
    // ========================================
    $this->forge->addField([
      'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
      'query' => ['type' => 'VARCHAR', 'constraint' => 500],
      'impressions' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'clicks' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
      'avg_position' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'default' => 0],
      'ctr' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
      'opportunity_zone' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'comment' => 'high_priority, medium_priority, monitor'],
      'period_start' => ['type' => 'DATE', 'null' => true],
      'period_end' => ['type' => 'DATE', 'null' => true],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('impressions');
    $this->forge->addKey('opportunity_zone');
    $this->forge->createTable('seo_bing_analytics', true);

    $this->db->query('CREATE INDEX idx_bing_query ON seo_bing_analytics (query(100))');
  }

  public function down()
  {
    $this->forge->dropTable('seo_bing_analytics', true);
    $this->forge->dropTable('seo_action_log', true);
    $this->forge->dropTable('seo_opportunities', true);
    $this->forge->dropTable('seo_queries', true);
  }
}
