<?php

/**
 * Helper de SEO para DoarFazBem
 * Funções auxiliares para meta tags, schema.org, etc.
 */

if (!function_exists('seo_meta')) {
  /**
   * Gera meta tags SEO completas
   */
  function seo_meta(array $meta): string
  {
    $html = '';

    // Title
    if (!empty($meta['title'])) {
      $html .= '<title>' . esc($meta['title']) . '</title>' . PHP_EOL;
    }

    // Description
    if (!empty($meta['description'])) {
      $html .= '<meta name="description" content="' . esc($meta['description']) . '">' . PHP_EOL;
    }

    // Keywords
    if (!empty($meta['keywords'])) {
      $html .= '<meta name="keywords" content="' . esc($meta['keywords']) . '">' . PHP_EOL;
    }

    // Robots
    if (!empty($meta['robots'])) {
      $html .= '<meta name="robots" content="' . esc($meta['robots']) . '">' . PHP_EOL;
    }

    // Canonical
    if (!empty($meta['canonical'])) {
      $html .= '<link rel="canonical" href="' . esc($meta['canonical']) . '">' . PHP_EOL;
    }

    // Open Graph
    if (!empty($meta['og_type'])) {
      $html .= '<meta property="og:type" content="' . esc($meta['og_type']) . '">' . PHP_EOL;
    }
    if (!empty($meta['og_url'])) {
      $html .= '<meta property="og:url" content="' . esc($meta['og_url']) . '">' . PHP_EOL;
    }
    if (!empty($meta['og_title'])) {
      $html .= '<meta property="og:title" content="' . esc($meta['og_title']) . '">' . PHP_EOL;
    }
    if (!empty($meta['og_description'])) {
      $html .= '<meta property="og:description" content="' . esc($meta['og_description']) . '">' . PHP_EOL;
    }
    if (!empty($meta['og_image'])) {
      $html .= '<meta property="og:image" content="' . esc($meta['og_image']) . '">' . PHP_EOL;
    }
    $html .= '<meta property="og:site_name" content="DoarFazBem">' . PHP_EOL;
    $html .= '<meta property="og:locale" content="pt_BR">' . PHP_EOL;

    // Twitter Card
    if (!empty($meta['twitter_card'])) {
      $html .= '<meta name="twitter:card" content="' . esc($meta['twitter_card']) . '">' . PHP_EOL;
    }
    if (!empty($meta['twitter_title'])) {
      $html .= '<meta name="twitter:title" content="' . esc($meta['twitter_title']) . '">' . PHP_EOL;
    }
    if (!empty($meta['twitter_desc'])) {
      $html .= '<meta name="twitter:description" content="' . esc($meta['twitter_desc']) . '">' . PHP_EOL;
    }
    if (!empty($meta['twitter_image'])) {
      $html .= '<meta name="twitter:image" content="' . esc($meta['twitter_image']) . '">' . PHP_EOL;
    }

    // Article meta (para blog posts)
    if (!empty($meta['article'])) {
      if (!empty($meta['article']['published_time'])) {
        $html .= '<meta property="article:published_time" content="' . esc($meta['article']['published_time']) . '">' . PHP_EOL;
      }
      if (!empty($meta['article']['modified_time'])) {
        $html .= '<meta property="article:modified_time" content="' . esc($meta['article']['modified_time']) . '">' . PHP_EOL;
      }
      if (!empty($meta['article']['section'])) {
        $html .= '<meta property="article:section" content="' . esc($meta['article']['section']) . '">' . PHP_EOL;
      }
    }

    return $html;
  }
}

if (!function_exists('seo_breadcrumb_html')) {
  /**
   * Gera HTML visual de breadcrumb
   */
  function seo_breadcrumb_html(array $items): string
  {
    $html = '<nav aria-label="Breadcrumb" class="mb-4">';
    $html .= '<ol class="flex items-center space-x-2 text-sm text-gray-500">';

    foreach ($items as $i => $item) {
      if ($i > 0) {
        $html .= '<li><i class="fas fa-chevron-right text-xs mx-1"></i></li>';
      }
      if (!empty($item['url']) && $i < count($items) - 1) {
        $html .= '<li><a href="' . esc($item['url']) . '" class="hover:text-emerald-600 transition">' . esc($item['name']) . '</a></li>';
      } else {
        $html .= '<li class="text-gray-700 font-medium">' . esc($item['name']) . '</li>';
      }
    }

    $html .= '</ol></nav>';
    return $html;
  }
}

if (!function_exists('reading_time')) {
  /**
   * Calcula tempo de leitura
   */
  function reading_time(string $content): int
  {
    $wordCount = str_word_count(strip_tags($content));
    return max(1, ceil($wordCount / 200));
  }
}

if (!function_exists('generate_slug')) {
  /**
   * Gera slug a partir de string
   */
  function generate_slug(string $text): string
  {
    return url_title($text, '-', true);
  }
}
