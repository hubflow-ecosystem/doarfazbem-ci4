<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * BlogPostModel - Artigos do Blog DoarFazBem
 */
class BlogPostModel extends Model
{
  protected $table = 'blog_posts';
  protected $primaryKey = 'id';
  protected $useAutoIncrement = true;
  protected $returnType = 'array';
  protected $useSoftDeletes = true;
  protected $protectFields = true;

  protected $allowedFields = [
    'title', 'slug', 'excerpt', 'content', 'featured_image', 'images',
    'image_alt', 'image_caption', 'image_credit', 'category_id',
    'author_id', 'author_name', 'status', 'published_at', 'scheduled_at',
    'meta_title', 'meta_description', 'meta_keywords', 'reading_time',
    'views_count', 'likes_count', 'shares_count', 'source', 'tags',
    'is_featured', 'allow_comments',
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $deletedField = 'deleted_at';

  protected $validationRules = [
    'title' => 'required|min_length[5]|max_length[255]',
    'slug' => 'required|alpha_dash|is_unique[blog_posts.slug,id,{id}]',
    'content' => 'required',
  ];

  // ========================================
  // QUERIES PUBLICAS
  // ========================================

  public function getPublished(int $limit = 10, int $offset = 0): array
  {
    return $this->select('blog_posts.*, blog_categories.name as category_name, blog_categories.slug as category_slug')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
      ->where('blog_posts.status', 'published')
      ->where('blog_posts.published_at <=', date('Y-m-d H:i:s'))
      ->orderBy('blog_posts.published_at', 'DESC')
      ->findAll($limit, $offset);
  }

  public function getBySlug(string $slug): ?array
  {
    return $this->where('slug', $slug)
      ->where('status', 'published')
      ->first();
  }

  public function getByCategory(int $categoryId, int $limit = 10): array
  {
    return $this->where('category_id', $categoryId)
      ->where('status', 'published')
      ->orderBy('published_at', 'DESC')
      ->findAll($limit);
  }

  public function getByCategoryIds(array $categoryIds, int $limit = 10, int $offset = 0): array
  {
    if (empty($categoryIds)) return [];
    return $this->select('blog_posts.*, blog_categories.name as category_name, blog_categories.slug as category_slug')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
      ->whereIn('category_id', $categoryIds)
      ->where('status', 'published')
      ->where('published_at <=', date('Y-m-d H:i:s'))
      ->orderBy('published_at', 'DESC')
      ->findAll($limit, $offset);
  }

  public function getRelated(int $postId, int $categoryId, int $limit = 3): array
  {
    return $this->where('id !=', $postId)
      ->where('category_id', $categoryId)
      ->where('status', 'published')
      ->orderBy('published_at', 'DESC')
      ->findAll($limit);
  }

  public function getPopular(int $limit = 5): array
  {
    return $this->select('blog_posts.*, blog_categories.name as category_name, blog_categories.slug as category_slug')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
      ->where('blog_posts.status', 'published')
      ->where('blog_posts.published_at <=', date('Y-m-d H:i:s'))
      ->orderBy('blog_posts.views_count', 'DESC')
      ->findAll($limit);
  }

  public function getFeatured(int $limit = 3): array
  {
    return $this->select('blog_posts.*, blog_categories.name as category_name, blog_categories.slug as category_slug')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
      ->where('blog_posts.status', 'published')
      ->where('blog_posts.is_featured', 1)
      ->orderBy('blog_posts.published_at', 'DESC')
      ->findAll($limit);
  }

  public function search(string $term, int $limit = 10): array
  {
    return $this->select('blog_posts.*, blog_categories.name as category_name, blog_categories.slug as category_slug')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
      ->groupStart()
        ->like('blog_posts.title', $term)
        ->orLike('blog_posts.content', $term)
        ->orLike('blog_posts.excerpt', $term)
      ->groupEnd()
      ->where('blog_posts.status', 'published')
      ->orderBy('blog_posts.published_at', 'DESC')
      ->findAll($limit);
  }

  public function countPublished(): int
  {
    return $this->where('status', 'published')
      ->where('published_at <=', date('Y-m-d H:i:s'))
      ->countAllResults();
  }

  // ========================================
  // ADMIN QUERIES
  // ========================================

  public function getAllForAdmin(array $filters = []): array
  {
    $builder = $this->select('blog_posts.*, blog_categories.name as category_name')
      ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left');

    if (!empty($filters['status'])) {
      $builder->where('blog_posts.status', $filters['status']);
    }
    if (!empty($filters['category_id'])) {
      $builder->where('blog_posts.category_id', $filters['category_id']);
    }
    if (!empty($filters['search'])) {
      $builder->groupStart()
        ->like('blog_posts.title', $filters['search'])
        ->orLike('blog_posts.content', $filters['search'])
        ->groupEnd();
    }

    return $builder->orderBy('blog_posts.created_at', 'DESC')->findAll();
  }

  // ========================================
  // UTILS
  // ========================================

  public function incrementViews(int $id): bool
  {
    return $this->set('views_count', 'views_count + 1', false)
      ->where('id', $id)->update();
  }

  public function generateUniqueSlug(string $title): string
  {
    $slug = url_title($title, '-', true);
    $originalSlug = $slug;
    $counter = 1;
    while ($this->where('slug', $slug)->first()) {
      $slug = $originalSlug . '-' . $counter++;
    }
    return $slug;
  }

  public function publishScheduled(): int
  {
    $now = date('Y-m-d H:i:s');
    $scheduled = $this->where('status', 'scheduled')
      ->where('scheduled_at <=', $now)->findAll();
    $count = 0;
    foreach ($scheduled as $post) {
      $this->update($post['id'], ['status' => 'published', 'published_at' => $post['scheduled_at']]);
      $count++;
    }
    return $count;
  }
}
