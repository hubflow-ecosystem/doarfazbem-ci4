<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * BlogCategoryModel - Categorias do Blog DoarFazBem
 * Suporta hierarquia pai/filhos
 */
class BlogCategoryModel extends Model
{
  protected $table = 'blog_categories';
  protected $primaryKey = 'id';
  protected $returnType = 'array';
  protected $protectFields = true;

  protected $allowedFields = [
    'parent_id', 'name', 'slug', 'description', 'icon', 'color',
    'featured_image', 'meta_title', 'meta_description', 'posts_count',
    'sort_order', 'is_active',
  ];

  protected $useTimestamps = true;

  public function getActive(): array
  {
    return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
  }

  public function getBySlug(string $slug): ?array
  {
    return $this->where('slug', $slug)->first();
  }

  public function getWithPostCount(): array
  {
    return $this->db->query("
      SELECT c.*, COUNT(p.id) as post_count
      FROM blog_categories c
      LEFT JOIN blog_posts p ON p.category_id = c.id AND p.status = 'published' AND p.deleted_at IS NULL
      WHERE c.is_active = 1
      GROUP BY c.id
      ORDER BY c.sort_order ASC
    ")->getResultArray();
  }

  public function getParentCategories(bool $activeOnly = true): array
  {
    $builder = $this->builder()
      ->groupStart()
        ->where('parent_id IS NULL')
        ->orWhere('parent_id', 0)
      ->groupEnd();
    if ($activeOnly) $builder->where('is_active', 1);
    return $builder->orderBy('sort_order', 'ASC')->get()->getResultArray();
  }

  public function getSubcategories(int $parentId): array
  {
    return $this->where('parent_id', $parentId)
      ->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
  }

  public function getWithChildren(): array
  {
    $all = $this->where('is_active', 1)->orderBy('parent_id', 'ASC')
      ->orderBy('sort_order', 'ASC')->findAll();
    $parents = [];
    $children = [];
    foreach ($all as $cat) {
      $pid = $cat['parent_id'] ?? null;
      if ($pid === null || $pid === 0 || $pid === '0' || $pid === '') {
        $cat['children'] = [];
        $parents[$cat['id']] = $cat;
      } else {
        $children[] = $cat;
      }
    }
    foreach ($children as $child) {
      if (isset($parents[$child['parent_id']])) {
        $parents[$child['parent_id']]['children'][] = $child;
      }
    }
    return array_values($parents);
  }

  public function getCategoryAndChildrenIds(int $categoryId): array
  {
    $ids = [$categoryId];
    $children = $this->where('parent_id', $categoryId)->where('is_active', 1)->findAll();
    foreach ($children as $child) $ids[] = $child['id'];
    return $ids;
  }

  public function getBreadcrumb($id): array
  {
    $cat = is_numeric($id) ? $this->find($id) : $this->getBySlug($id);
    if (!$cat) return [];
    $breadcrumb = [$cat];
    while (!empty($cat['parent_id'])) {
      $parent = $this->find($cat['parent_id']);
      if (!$parent) break;
      array_unshift($breadcrumb, $parent);
      $cat = $parent;
    }
    return $breadcrumb;
  }

  public function updatePostsCount(int $categoryId): int
  {
    $count = $this->db->table('blog_posts')
      ->where('category_id', $categoryId)
      ->where('status', 'published')
      ->where('deleted_at IS NULL')
      ->countAllResults();
    $this->update($categoryId, ['posts_count' => $count]);
    return $count;
  }

  /**
   * Categorias padrão do DoarFazBem
   */
  public static function getDefaultCategories(): array
  {
    return [
      ['name' => 'Doações e Solidariedade', 'slug' => 'doacoes-e-solidariedade', 'description' => 'Dicas e histórias sobre o poder da doação', 'icon' => 'heart', 'color' => '#10B981', 'sort_order' => 1],
      ['name' => 'Histórias de Sucesso', 'slug' => 'historias-de-sucesso', 'description' => 'Campanhas que alcançaram seus objetivos', 'icon' => 'star', 'color' => '#F59E0B', 'sort_order' => 2],
      ['name' => 'Transparência', 'slug' => 'transparencia', 'description' => 'Como garantimos a segurança das doações', 'icon' => 'shield-check', 'color' => '#3B82F6', 'sort_order' => 3],
      ['name' => 'Dicas para Campanhas', 'slug' => 'dicas-para-campanhas', 'description' => 'Como criar e divulgar sua campanha', 'icon' => 'lightbulb', 'color' => '#8B5CF6', 'sort_order' => 4],
      ['name' => 'Impacto Social', 'slug' => 'impacto-social', 'description' => 'O impacto das doações na comunidade', 'icon' => 'globe', 'color' => '#EC4899', 'sort_order' => 5],
      ['name' => 'Notícias', 'slug' => 'noticias', 'description' => 'Novidades e atualizações da plataforma', 'icon' => 'newspaper', 'color' => '#06B6D4', 'sort_order' => 6],
      ['name' => 'Rifas e Sorteios', 'slug' => 'rifas-e-sorteios', 'description' => 'Tudo sobre rifas solidárias', 'icon' => 'ticket', 'color' => '#EF4444', 'sort_order' => 7],
    ];
  }
}
