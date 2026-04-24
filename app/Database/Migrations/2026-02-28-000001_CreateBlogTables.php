<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cria tabelas do blog: categorias e posts
 */
class CreateBlogTables extends Migration
{
  public function up()
  {
    // Tabela de categorias do blog
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'parent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
      'name' => ['type' => 'VARCHAR', 'constraint' => 255],
      'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
      'description' => ['type' => 'TEXT', 'null' => true],
      'icon' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
      'color' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
      'featured_image' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'meta_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'meta_description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'posts_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addKey('slug');
    $this->forge->addKey('parent_id');
    $this->forge->addKey('is_active');
    $this->forge->createTable('blog_categories', true);

    // Tabela de posts do blog
    $this->forge->addField([
      'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
      'title' => ['type' => 'VARCHAR', 'constraint' => 255],
      'slug' => ['type' => 'VARCHAR', 'constraint' => 255],
      'excerpt' => ['type' => 'TEXT', 'null' => true],
      'content' => ['type' => 'LONGTEXT'],
      'featured_image' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'images' => ['type' => 'JSON', 'null' => true],
      'image_alt' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'image_caption' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'image_credit' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'category_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
      'author_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
      'author_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'scheduled'], 'default' => 'draft'],
      'published_at' => ['type' => 'DATETIME', 'null' => true],
      'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
      'meta_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
      'meta_description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'meta_keywords' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
      'reading_time' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
      'views_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'likes_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'shares_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
      'source' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'manual'],
      'tags' => ['type' => 'JSON', 'null' => true],
      'is_featured' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
      'allow_comments' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
      'created_at' => ['type' => 'DATETIME', 'null' => true],
      'updated_at' => ['type' => 'DATETIME', 'null' => true],
      'deleted_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('slug');
    $this->forge->addKey('category_id');
    $this->forge->addKey('author_id');
    $this->forge->addKey('status');
    $this->forge->addKey('published_at');
    $this->forge->addKey('is_featured');
    $this->forge->createTable('blog_posts', true);
  }

  public function down()
  {
    $this->forge->dropTable('blog_posts', true);
    $this->forge->dropTable('blog_categories', true);
  }
}
