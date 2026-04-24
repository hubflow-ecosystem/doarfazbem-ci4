<?php

namespace App\Libraries;

/**
 * SchemaMarkupService - Geração de Schema.org JSON-LD para DoarFazBem
 *
 * Gera schemas para:
 * - Organization
 * - WebSite + SiteLinksSearchBox
 * - Article (blog)
 * - DonateAction (campanhas)
 * - Event (rifas/sorteios)
 * - FAQPage
 * - BreadcrumbList
 * - ItemList (listagens)
 */
class SchemaMarkupService
{
  /**
   * Schema da organização DoarFazBem
   */
  public function forOrganization(): string
  {
    return $this->toJson([
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => 'DoarFazBem',
      'url' => base_url(),
      'logo' => base_url('assets/images/logo.png'),
      'description' => 'A plataforma de crowdfunding mais justa do Brasil. Campanhas médicas 100% gratuitas.',
      'foundingDate' => '2024',
      'sameAs' => [
        'https://facebook.com/doarfazbem',
        'https://instagram.com/doarfazbem',
      ],
      'contactPoint' => [
        '@type' => 'ContactPoint',
        'contactType' => 'Customer Service',
        'email' => 'contato@doarfazbem.com.br',
        'availableLanguage' => 'Portuguese',
      ],
    ]);
  }

  /**
   * Schema para WebSite com SearchAction
   */
  public function forWebsite(): string
  {
    return $this->toJson([
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => 'DoarFazBem',
      'url' => base_url(),
      'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
          '@type' => 'EntryPoint',
          'urlTemplate' => base_url('campaigns?q={search_term_string}'),
        ],
        'query-input' => 'required name=search_term_string',
      ],
    ]);
  }

  /**
   * Schema para artigo do blog
   */
  public function forArticle(array $post, ?array $category = null): string
  {
    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'Article',
      'headline' => $post['meta_title'] ?? $post['title'],
      'description' => $post['meta_description'] ?? $post['excerpt'] ?? '',
      'url' => base_url('blog/' . $post['slug']),
      'datePublished' => $post['published_at'] ?? $post['created_at'],
      'dateModified' => $post['updated_at'] ?? $post['published_at'],
      'inLanguage' => 'pt-BR',
      'author' => [
        '@type' => 'Organization',
        'name' => $post['author_name'] ?? 'DoarFazBem',
        'url' => base_url(),
      ],
      'publisher' => [
        '@type' => 'Organization',
        'name' => 'DoarFazBem',
        'logo' => [
          '@type' => 'ImageObject',
          'url' => base_url('assets/images/logo.png'),
        ],
      ],
      'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => base_url('blog/' . $post['slug']),
      ],
    ];

    if (!empty($post['featured_image'])) {
      $schema['image'] = [
        '@type' => 'ImageObject',
        'url' => $post['featured_image'],
        'caption' => $post['image_caption'] ?? $post['title'],
      ];
    }

    if ($category) {
      $schema['articleSection'] = $category['name'];
    }

    if (!empty($post['reading_time'])) {
      $schema['timeRequired'] = 'PT' . $post['reading_time'] . 'M';
    }

    if (!empty($post['meta_keywords'])) {
      $schema['keywords'] = $post['meta_keywords'];
    }

    return $this->toJson($schema);
  }

  /**
   * Schema para campanha de doação (DonateAction)
   */
  public function forCampaign(array $campaign): string
  {
    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'DonateAction',
      'name' => $campaign['title'],
      'description' => $campaign['description'] ?? '',
      'url' => base_url('campaigns/' . ($campaign['slug'] ?? $campaign['id'])),
      'recipient' => [
        '@type' => 'Organization',
        'name' => $campaign['creator_name'] ?? 'DoarFazBem',
      ],
      'agent' => [
        '@type' => 'Organization',
        'name' => 'DoarFazBem',
        'url' => base_url(),
      ],
    ];

    if (!empty($campaign['goal'])) {
      $schema['price'] = $campaign['goal'];
      $schema['priceCurrency'] = 'BRL';
    }

    if (!empty($campaign['image'])) {
      $schema['image'] = $campaign['image'];
    }

    return $this->toJson($schema);
  }

  /**
   * Schema para FAQPage
   */
  public function forFaq(array $faqs, string $url = ''): string
  {
    if (empty($faqs)) return '';

    $faqEntries = [];
    foreach ($faqs as $faq) {
      $faqEntries[] = [
        '@type' => 'Question',
        'name' => $faq['question'],
        'acceptedAnswer' => [
          '@type' => 'Answer',
          'text' => $faq['answer'],
        ],
      ];
    }

    return $this->toJson([
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'url' => $url ?: current_url(),
      'mainEntity' => $faqEntries,
    ]);
  }

  /**
   * Schema de BreadcrumbList
   */
  public function forBreadcrumb(array $items): string
  {
    $listItems = [];
    foreach ($items as $i => $item) {
      $listItems[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $item['name'],
        'item' => $item['url'] ?? '',
      ];
    }

    return $this->toJson([
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => $listItems,
    ]);
  }

  /**
   * Schema para listagem de campanhas (ItemList)
   */
  public function forCampaignList(array $campaigns): string
  {
    $items = [];
    foreach ($campaigns as $i => $campaign) {
      $items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => base_url('campaigns/' . ($campaign['slug'] ?? $campaign['id'])),
        'name' => $campaign['title'],
      ];
    }

    return $this->toJson([
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'name' => 'Campanhas de Doação — DoarFazBem',
      'numberOfItems' => count($items),
      'itemListElement' => $items,
    ]);
  }

  private function toJson(array $data): string
  {
    return '<script type="application/ld+json">' . PHP_EOL
      . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
      . PHP_EOL . '</script>';
  }
}
