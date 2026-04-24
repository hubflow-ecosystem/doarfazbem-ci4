<?php

namespace App\Libraries;

use App\Models\CampaignModel;
use App\Models\BlogPostModel;

/**
 * RelatedContentService - Conteúdo relacionado entre entidades
 *
 * Fornece widgets de conteúdo cruzado:
 * - Campanhas relacionadas em artigos do blog
 * - Artigos do blog em páginas de campanhas
 * - Rifa ativa para sidebar
 */
class RelatedContentService
{
  private const CACHE_PREFIX = 'related_content_';
  private const CACHE_TTL = 1800; // 30 minutos

  // Mapeamento estático: categoria do blog → categorias de campanha
  private const BLOG_TO_CAMPAIGN = [
    'doacoes-e-solidariedade' => ['social', 'medica'],
    'historias-de-sucesso' => null, // todas as categorias
    'transparencia' => null,
    'dicas-para-campanhas' => null,
    'impacto-social' => ['social', 'educacao'],
    'noticias' => null,
    'rifas-e-sorteios' => '__raffle__', // widget de rifa em vez de campanha
  ];

  // Mapeamento inverso: categoria da campanha → slugs de blog
  private const CAMPAIGN_TO_BLOG = [
    'medica' => ['doacoes-e-solidariedade', 'impacto-social'],
    'social' => ['doacoes-e-solidariedade', 'impacto-social', 'historias-de-sucesso'],
    'criativa' => ['dicas-para-campanhas', 'historias-de-sucesso'],
    'negocio' => ['dicas-para-campanhas'],
    'educacao' => ['impacto-social', 'dicas-para-campanhas'],
  ];

  /**
   * Retorna campanhas ativas relacionadas a um post do blog
   */
  public function getRelatedCampaignsForPost(array $post, int $limit = 3): array
  {
    $cacheKey = self::CACHE_PREFIX . 'camp_post_' . ($post['id'] ?? 0);
    $cached = cache($cacheKey);
    if ($cached !== null && $cached !== false) return $cached;

    $categorySlug = $post['category_slug'] ?? '';

    // Categoria de rifas → não retorna campanhas (tem widget próprio)
    if ($categorySlug === 'rifas-e-sorteios') {
      cache()->save($cacheKey, [], self::CACHE_TTL);
      return [];
    }

    // Verificar se há mapeamento no banco que sobrescreve o estático
    $dbMap = $this->getDbCategoryMap($categorySlug);

    try {
      $campaignModel = new CampaignModel();
      $builder = $campaignModel->where('status', 'active');

      if ($dbMap && $dbMap['entity_type'] === 'campaign_category') {
        $builder->where('category', $dbMap['entity_value']);
        $limit = $dbMap['widget_limit'] ?? $limit;
      } elseif (isset(self::BLOG_TO_CAMPAIGN[$categorySlug])) {
        $targetCats = self::BLOG_TO_CAMPAIGN[$categorySlug];
        if ($targetCats !== null && $targetCats !== '__raffle__') {
          $builder->whereIn('category', $targetCats);
        }
      }

      $campaigns = $builder
        ->orderBy('is_featured', 'DESC')
        ->orderBy('created_at', 'DESC')
        ->findAll($limit);
    } catch (\Throwable $e) {
      $campaigns = [];
    }

    cache()->save($cacheKey, $campaigns, self::CACHE_TTL);
    return $campaigns;
  }

  /**
   * Retorna artigos do blog relacionados a uma campanha
   */
  public function getRelatedPostsForCampaign(array $campaign, int $limit = 3): array
  {
    $cacheKey = self::CACHE_PREFIX . 'posts_camp_' . ($campaign['id'] ?? 0);
    $cached = cache($cacheKey);
    if ($cached !== null && $cached !== false) return $cached;

    $campaignCategory = $campaign['category'] ?? '';
    $blogSlugs = self::CAMPAIGN_TO_BLOG[$campaignCategory] ?? [];

    try {
      $postModel = new BlogPostModel();

      if (!empty($blogSlugs)) {
        // Buscar IDs das categorias do blog
        $db = db_connect();
        $cats = $db->table('blog_categories')
          ->whereIn('slug', $blogSlugs)
          ->where('is_active', 1)
          ->get()->getResultArray();

        $catIds = array_column($cats, 'id');
        if (!empty($catIds)) {
          $posts = $postModel->getByCategoryIds($catIds, $limit);
          cache()->save($cacheKey, $posts, self::CACHE_TTL);
          return $posts;
        }
      }

      // Fallback: posts mais recentes
      $posts = $postModel->getPublished($limit);
    } catch (\Throwable $e) {
      $posts = [];
    }

    cache()->save($cacheKey, $posts, self::CACHE_TTL);
    return $posts;
  }

  /**
   * Retorna a rifa ativa para o widget na sidebar
   */
  public function getActiveRaffleWidget(): ?array
  {
    $cacheKey = self::CACHE_PREFIX . 'active_raffle';
    $cached = cache($cacheKey);
    if ($cached !== null && $cached !== false) return $cached ?: null;

    try {
      $db = db_connect();
      if (!in_array('raffles', $db->listTables())) return null;

      $raffle = $db->table('raffles')
        ->where('status', 'active')
        ->orderBy('created_at', 'DESC')
        ->limit(1)
        ->get()->getRowArray();

      cache()->save($cacheKey, $raffle ?? [], 900); // 15 min
      return $raffle;
    } catch (\Throwable $e) {
      return null;
    }
  }

  /**
   * Busca mapeamento de categoria no banco (override do mapeamento estático)
   */
  private function getDbCategoryMap(string $blogCategorySlug): ?array
  {
    if (empty($blogCategorySlug)) return null;

    try {
      $db = db_connect();
      if (!in_array('seo_link_category_map', $db->listTables())) return null;

      return $db->table('seo_link_category_map')
        ->where('blog_category_slug', $blogCategorySlug)
        ->where('is_active', 1)
        ->get()->getRowArray();
    } catch (\Throwable $e) {
      return null;
    }
  }
}
