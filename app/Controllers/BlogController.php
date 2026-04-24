<?php

namespace App\Controllers;

use App\Models\BlogPostModel;
use App\Models\BlogCategoryModel;
use App\Libraries\SeoMetaService;
use App\Libraries\SchemaMarkupService;
use App\Libraries\AutoLinkerService;
use App\Libraries\RelatedContentService;

/**
 * BlogController - Blog Público do DoarFazBem
 *
 * URL: /blog
 */
class BlogController extends BaseController
{
  protected BlogPostModel $postModel;
  protected BlogCategoryModel $categoryModel;
  protected SeoMetaService $seoService;
  protected SchemaMarkupService $schemaService;

  public function __construct()
  {
    $this->postModel = new BlogPostModel();
    $this->categoryModel = new BlogCategoryModel();
    $this->seoService = new SeoMetaService();
    $this->schemaService = new SchemaMarkupService();
    helper(['seo', 'blog']);
  }

  /**
   * Página inicial do blog
   * GET /blog
   */
  public function index()
  {
    $page = max(1, (int)($this->request->getGet('page') ?? 1));
    $perPage = 9;

    $posts = $this->postModel->getPublished($perPage, ($page - 1) * $perPage);
    $totalPosts = $this->postModel->countPublished();
    $categories = $this->categoryModel->getWithPostCount();
    $popularPosts = $this->postModel->getPopular(5);
    $featuredPosts = $this->postModel->getFeatured(3);

    $seoMeta = $this->seoService->generate('blog_index', [], base_url('blog'));
    $breadcrumbSchema = $this->schemaService->forBreadcrumb([
      ['name' => 'Início', 'url' => base_url()],
      ['name' => 'Blog', 'url' => base_url('blog')],
    ]);

    return view('blog/index', [
      'title' => $seoMeta['title'],
      'seoMeta' => $seoMeta,
      'breadcrumbSchema' => $breadcrumbSchema,
      'posts' => $posts,
      'featuredPosts' => $featuredPosts,
      'categories' => $categories,
      'popularPosts' => $popularPosts,
      'currentPage' => $page,
      'totalPages' => ceil($totalPosts / $perPage),
      'totalPosts' => $totalPosts,
    ]);
  }

  /**
   * Visualizar artigo
   * GET /blog/:slug
   */
  public function view(string $slug)
  {
    $post = $this->postModel->getBySlug($slug);
    if (!$post) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Artigo não encontrado');

    $this->postModel->incrementViews($post['id']);

    $category = $post['category_id'] ? $this->categoryModel->find($post['category_id']) : null;
    $relatedPosts = $post['category_id'] ? $this->postModel->getRelated($post['id'], $post['category_id'], 3) : [];
    $categories = $this->categoryModel->getActive();

    // SEO automático
    $seoMeta = $this->seoService->generate('blog', [
      'title' => $post['meta_title'] ?? $post['title'],
      'descricao' => $post['meta_description'] ?? blog_excerpt($post['content'], 160),
      'keywords' => $post['meta_keywords'] ?? '',
    ], base_url('blog/' . $slug), [
      'og_image' => $post['featured_image'] ?? null,
      'og_type' => 'article',
      'article' => [
        'published_time' => $post['published_at'],
        'modified_time' => $post['updated_at'],
        'section' => $category['name'] ?? '',
      ],
    ]);

    // Schema.org Article
    $articleSchema = $this->schemaService->forArticle($post, $category);

    // Breadcrumb
    $breadcrumbItems = [
      ['name' => 'Início', 'url' => base_url()],
      ['name' => 'Blog', 'url' => base_url('blog')],
    ];
    if ($category) {
      $breadcrumbItems[] = ['name' => $category['name'], 'url' => base_url('blog/categoria/' . $category['slug'])];
    }
    $breadcrumbItems[] = ['name' => $post['title']];
    $breadcrumbSchema = $this->schemaService->forBreadcrumb($breadcrumbItems);

    // FAQs se existirem
    $faqSchema = '';
    try {
      $faqs = db_connect()->table('seo_faqs')
        ->where('page_type', 'blog')->where('page_identifier', $slug)
        ->where('is_active', 1)->orderBy('sort_order', 'ASC')
        ->get()->getResultArray();
      if (!empty($faqs)) $faqSchema = $this->schemaService->forFaq($faqs, base_url('blog/' . $slug));
    } catch (\Throwable $e) {}

    // Auto-linking de keywords no conteúdo
    $autoLinker = new AutoLinkerService();
    $linkedContent = $autoLinker->processContent($post['content'], $category['slug'] ?? null);

    // Conteúdo relacionado cross-entity
    $relatedService = new RelatedContentService();
    $relatedCampaigns = $relatedService->getRelatedCampaignsForPost($post, 3);
    $raffleWidget = $relatedService->getActiveRaffleWidget();

    return view('blog/view', [
      'title' => $seoMeta['title'],
      'seoMeta' => $seoMeta,
      'articleSchema' => $articleSchema,
      'breadcrumbSchema' => $breadcrumbSchema,
      'faqSchema' => $faqSchema,
      'post' => $post,
      'linkedContent' => $linkedContent,
      'category' => $category,
      'relatedPosts' => $relatedPosts,
      'relatedCampaigns' => $relatedCampaigns,
      'raffleWidget' => $raffleWidget,
      'categories' => $categories,
    ]);
  }

  /**
   * Artigos por categoria
   * GET /blog/categoria/:slug
   */
  public function category(string $slug)
  {
    $category = $this->categoryModel->getBySlug($slug);
    if (!$category) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Categoria não encontrada');

    $page = max(1, (int)($this->request->getGet('page') ?? 1));
    $categoryIds = $this->categoryModel->getCategoryAndChildrenIds($category['id']);
    $posts = $this->postModel->getByCategoryIds($categoryIds, 9, ($page - 1) * 9);
    $categories = $this->categoryModel->getWithPostCount();

    $seoMeta = $this->seoService->generate('blog_category', [
      'title' => $category['meta_title'] ?? $category['name'],
      'descricao' => $category['meta_description'] ?? $category['description'] ?? '',
    ], base_url('blog/categoria/' . $slug));

    $breadcrumbSchema = $this->schemaService->forBreadcrumb([
      ['name' => 'Início', 'url' => base_url()],
      ['name' => 'Blog', 'url' => base_url('blog')],
      ['name' => $category['name']],
    ]);

    return view('blog/category', [
      'title' => $seoMeta['title'],
      'seoMeta' => $seoMeta,
      'breadcrumbSchema' => $breadcrumbSchema,
      'category' => $category,
      'posts' => $posts,
      'categories' => $categories,
      'currentPage' => $page,
    ]);
  }

  /**
   * Buscar artigos
   * GET /blog/buscar
   */
  public function search()
  {
    $term = $this->request->getGet('q') ?? '';
    if (empty($term)) return redirect()->to('/blog');

    $posts = $this->postModel->search($term, 20);
    $categories = $this->categoryModel->getActive();

    return view('blog/search', [
      'title' => "Busca: {$term} — Blog DoarFazBem",
      'searchTerm' => $term,
      'posts' => $posts,
      'categories' => $categories,
    ]);
  }

  /**
   * Feed RSS
   * GET /blog/feed
   */
  public function feed()
  {
    $posts = $this->postModel->getPublished(20);

    $this->response->setHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . PHP_EOL;
    $xml .= '<channel>' . PHP_EOL;
    $xml .= '<title>Blog DoarFazBem</title>' . PHP_EOL;
    $xml .= '<link>' . base_url('blog') . '</link>' . PHP_EOL;
    $xml .= '<description>Artigos sobre doações, solidariedade e impacto social</description>' . PHP_EOL;
    $xml .= '<language>pt-BR</language>' . PHP_EOL;
    $xml .= '<atom:link href="' . base_url('blog/feed') . '" rel="self" type="application/rss+xml" />' . PHP_EOL;

    foreach ($posts as $post) {
      $xml .= '<item>' . PHP_EOL;
      $xml .= '<title>' . esc($post['title']) . '</title>' . PHP_EOL;
      $xml .= '<link>' . base_url('blog/' . $post['slug']) . '</link>' . PHP_EOL;
      $xml .= '<description>' . esc($post['excerpt'] ?? '') . '</description>' . PHP_EOL;
      $xml .= '<pubDate>' . date('r', strtotime($post['published_at'])) . '</pubDate>' . PHP_EOL;
      $xml .= '<guid>' . base_url('blog/' . $post['slug']) . '</guid>' . PHP_EOL;
      if (!empty($post['category_name'])) {
        $xml .= '<category>' . esc($post['category_name']) . '</category>' . PHP_EOL;
      }
      $xml .= '</item>' . PHP_EOL;
    }

    $xml .= '</channel></rss>';
    return $this->response->setBody($xml);
  }

  /**
   * Sitemap do blog
   * GET /sitemap-blog.xml
   */
  public function sitemap()
  {
    $posts = $this->postModel->getPublished(5000);
    $categories = $this->categoryModel->getActive();
    $baseUrl = rtrim(base_url(), '/');

    $this->response->setContentType('application/xml');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    // Página principal do blog
    $xml .= '<url><loc>' . esc($baseUrl . '/blog') . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . PHP_EOL;

    // Categorias
    foreach ($categories as $cat) {
      $xml .= '<url><loc>' . esc($baseUrl . '/blog/categoria/' . $cat['slug']) . '</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>' . PHP_EOL;
    }

    // Posts
    foreach ($posts as $post) {
      $lastmod = $post['updated_at'] ?? $post['published_at'];
      $xml .= '<url><loc>' . esc($baseUrl . '/blog/' . $post['slug']) . '</loc>';
      if ($lastmod) $xml .= '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>';
      $xml .= '<changefreq>monthly</changefreq><priority>0.6</priority></url>' . PHP_EOL;
    }

    $xml .= '</urlset>';
    return $this->response->setBody($xml);
  }
}
