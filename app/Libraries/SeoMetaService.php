<?php

namespace App\Libraries;

/**
 * SeoMetaService - Geração dinâmica de meta tags SEO para DoarFazBem
 *
 * Templates com variáveis dinâmicas (%title%, %ano%, etc.)
 * Suporta override por banco de dados (tabela seo_page_meta)
 */
class SeoMetaService
{
  private array $templates = [
    'campaign' => [
      'title'       => '%title% — Campanha de Doação | DoarFazBem %ano%',
      'description' => '%descricao%',
    ],
    'campaign_list' => [
      'title'       => 'Campanhas de Doação — Ajude Quem Precisa | DoarFazBem %ano%',
      'description' => 'Descubra campanhas de doação verificadas. PIX, boleto e cartão. Transparência total em cada doação. DoarFazBem.',
    ],
    'raffle' => [
      'title'       => '%title% — Rifa Solidária | DoarFazBem %ano%',
      'description' => 'Participe da rifa solidária %title%. Compre cotas a partir de R$2 e concorra a prêmios. Toda arrecadação vai para causas sociais.',
    ],
    'blog' => [
      'title'       => '%title% | Blog DoarFazBem %ano%',
      'description' => '%descricao%',
    ],
    'blog_index' => [
      'title'       => 'Blog DoarFazBem — Doações, Solidariedade e Impacto Social | %ano%',
      'description' => 'Artigos sobre doações, campanhas solidárias, transparência e impacto social. Dicas para criar campanhas de sucesso.',
    ],
    'blog_category' => [
      'title'       => '%title% — Blog DoarFazBem | %ano%',
      'description' => '%descricao%',
    ],
    'home' => [
      'title'       => 'DoarFazBem — A Plataforma de Crowdfunding Mais Justa do Brasil | %ano%',
      'description' => 'Crie campanhas de doação com taxas a partir de 0%. PIX, boleto e cartão. Transparência total. Campanhas médicas 100% gratuitas.',
    ],
    'about' => [
      'title'       => 'Sobre o DoarFazBem — Nossa Missão e Valores | %ano%',
      'description' => 'Conheça a história do DoarFazBem, a plataforma de crowdfunding mais justa do Brasil. Transparência, segurança e impacto social.',
    ],
    'sitemap' => [
      'title'       => 'Mapa do Site — DoarFazBem',
      'description' => 'Navegue por todas as páginas do DoarFazBem: campanhas, blog, rifas, páginas institucionais e mais.',
    ],
    'default' => [
      'title'       => '%title% | DoarFazBem',
      'description' => '%descricao%',
    ],
  ];

  private array $dbCache = [];

  /**
   * Gerar meta tags completas para uma página
   */
  public function generate(
    string $pageType,
    array $vars = [],
    string $canonical = '',
    array $options = []
  ): array {
    $override = $this->getDbOverride($canonical);
    $template = $this->templates[$pageType] ?? $this->templates['default'];

    $title       = $override['meta_title']       ?? $this->applyVars($template['title'], $vars);
    $description = $override['meta_description'] ?? $this->applyVars($template['description'], $vars);
    $robots      = $override['robots']           ?? ($options['noindex'] ?? false
      ? 'noindex, follow'
      : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');

    if (empty($canonical)) $canonical = current_url();
    $canonicalFinal = $override['canonical_url'] ?? $canonical;

    $ogImage = $options['og_image'] ?? base_url('assets/images/og-image.jpg');
    $ogType = $options['og_type'] ?? 'website';
    if (in_array($pageType, ['campaign', 'raffle', 'blog'])) $ogType = 'article';

    return [
      'title'          => $title,
      'description'    => $description,
      'canonical'      => $canonicalFinal,
      'robots'         => $robots,
      'og_title'       => $title,
      'og_description' => $description,
      'og_image'       => $ogImage,
      'og_type'        => $ogType,
      'og_url'         => $canonicalFinal,
      'twitter_card'   => 'summary_large_image',
      'twitter_title'  => $title,
      'twitter_desc'   => $description,
      'twitter_image'  => $ogImage,
      'article'        => $options['article'] ?? null,
      'keywords'       => $vars['keywords'] ?? '',
    ];
  }

  private function applyVars(string $template, array $vars): string
  {
    $defaults = [
      'site' => 'DoarFazBem', 'ano' => date('Y'), 'title' => '',
      'descricao' => '', 'keyword' => '', 'categoria' => '',
    ];
    $vars = array_merge($defaults, $vars);
    return preg_replace_callback('/%(\w+)%/', function ($m) use ($vars) {
      return $vars[$m[1]] ?? $m[0];
    }, $template);
  }

  private function getDbOverride(string $url): array
  {
    if (empty($url)) return [];
    if (array_key_exists($url, $this->dbCache)) return $this->dbCache[$url];
    try {
      $db = db_connect();
      if (!in_array('seo_page_meta', $db->listTables())) {
        $this->dbCache[$url] = [];
        return [];
      }
      $row = $db->table('seo_page_meta')
        ->where('canonical_url', $url)->where('is_active', 1)
        ->get()->getRowArray();
      $this->dbCache[$url] = $row ?: [];
    } catch (\Throwable $e) {
      $this->dbCache[$url] = [];
    }
    return $this->dbCache[$url];
  }
}
