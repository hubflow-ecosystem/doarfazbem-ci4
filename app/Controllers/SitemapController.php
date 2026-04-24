<?php

namespace App\Controllers;

use App\Models\BlogPostModel;
use App\Models\BlogCategoryModel;
use App\Models\CampaignModel;

/**
 * SitemapController - Geração dinâmica de Sitemap XML, llms.txt e /mapa-do-site
 */
class SitemapController extends BaseController
{
  private function getBaseUrl(): string
  {
    $host = $this->request->getServer('HTTP_HOST') ?: parse_url(base_url(), PHP_URL_HOST);
    $scheme = (!empty($this->request->getServer('HTTPS')) && $this->request->getServer('HTTPS') !== 'off') ? 'https' : 'http';
    return rtrim($scheme . '://' . $host, '/');
  }

  /**
   * Sitemap Index XML
   * GET /sitemap.xml
   */
  public function xml()
  {
    $baseUrl = $this->getBaseUrl();
    $today = gmdate('Y-m-d') . 'T00:00:00+00:00';

    $this->response->setContentType('application/xml');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    // Páginas estáticas
    $xml .= '  <sitemap><loc>' . esc($baseUrl . '/sitemap-pages.xml') . '</loc><lastmod>' . $today . '</lastmod></sitemap>' . PHP_EOL;

    // Campanhas
    $xml .= '  <sitemap><loc>' . esc($baseUrl . '/sitemap-campaigns.xml') . '</loc><lastmod>' . $today . '</lastmod></sitemap>' . PHP_EOL;

    // Blog
    $hasBlog = false;
    try {
      $db = db_connect();
      if (in_array('blog_posts', $db->listTables())) {
        $hasBlog = (int)$db->table('blog_posts')->where('status', 'published')->countAllResults() > 0;
      }
    } catch (\Throwable $e) {}

    if ($hasBlog) {
      $xml .= '  <sitemap><loc>' . esc($baseUrl . '/sitemap-blog.xml') . '</loc><lastmod>' . $today . '</lastmod></sitemap>' . PHP_EOL;
    }

    $xml .= '</sitemapindex>';
    return $this->response->setBody($xml);
  }

  /**
   * Sitemap de páginas estáticas
   * GET /sitemap-pages.xml
   */
  public function sitemapPages()
  {
    $baseUrl = $this->getBaseUrl();
    $this->response->setContentType('application/xml');

    $pages = [
      ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
      ['loc' => '/campaigns', 'priority' => '0.9', 'changefreq' => 'daily'],
      ['loc' => '/blog', 'priority' => '0.8', 'changefreq' => 'daily'],
      ['loc' => '/rifas', 'priority' => '0.8', 'changefreq' => 'daily'],
      ['loc' => '/sobre', 'priority' => '0.5', 'changefreq' => 'monthly'],
      ['loc' => '/como-funciona', 'priority' => '0.6', 'changefreq' => 'monthly'],
      ['loc' => '/termos', 'priority' => '0.3', 'changefreq' => 'yearly'],
      ['loc' => '/privacidade', 'priority' => '0.3', 'changefreq' => 'yearly'],
      ['loc' => '/mapa-do-site', 'priority' => '0.4', 'changefreq' => 'weekly'],
      ['loc' => '/register', 'priority' => '0.5', 'changefreq' => 'monthly'],
      ['loc' => '/login', 'priority' => '0.4', 'changefreq' => 'monthly'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    foreach ($pages as $p) {
      $xml .= '<url><loc>' . esc($baseUrl . $p['loc']) . '</loc>';
      $xml .= '<changefreq>' . $p['changefreq'] . '</changefreq>';
      $xml .= '<priority>' . $p['priority'] . '</priority></url>' . PHP_EOL;
    }

    $xml .= '</urlset>';
    return $this->response->setBody($xml);
  }

  /**
   * Sitemap de campanhas
   * GET /sitemap-campaigns.xml
   */
  public function sitemapCampaigns()
  {
    $baseUrl = $this->getBaseUrl();
    $this->response->setContentType('application/xml');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    try {
      $campaigns = (new CampaignModel())->where('status', 'active')->findAll();
      foreach ($campaigns as $c) {
        $slug = $c['slug'] ?? $c['id'];
        $lastmod = $c['updated_at'] ?? $c['created_at'] ?? date('Y-m-d');
        $xml .= '<url><loc>' . esc($baseUrl . '/campaigns/' . $slug) . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq><priority>0.7</priority></url>' . PHP_EOL;
      }
    } catch (\Throwable $e) {}

    $xml .= '</urlset>';
    return $this->response->setBody($xml);
  }

  /**
   * Mapa do site HTML
   * GET /mapa-do-site
   */
  public function mapaSite()
  {
    $categories = [];
    $blogPosts = [];
    $campaigns = [];

    try {
      $categories = (new BlogCategoryModel())->getActive();
    } catch (\Throwable $e) {}

    try {
      $blogPosts = (new BlogPostModel())->getPublished(50);
    } catch (\Throwable $e) {}

    try {
      $campaigns = (new CampaignModel())->where('status', 'active')->orderBy('created_at', 'DESC')->findAll(50);
    } catch (\Throwable $e) {}

    return view('pages/sitemap', [
      'title' => 'Mapa do Site — DoarFazBem',
      'categories' => $categories,
      'blogPosts' => $blogPosts,
      'campaigns' => $campaigns,
    ]);
  }

  /**
   * llms.txt dinâmico
   * GET /llms.txt
   */
  public function llmsTxt()
  {
    $this->response->setContentType('text/plain; charset=UTF-8');

    $totalCampaigns = 0;
    $totalDonations = 0;
    $totalUsers = 0;
    $totalBlogPosts = 0;

    $db = db_connect();
    try { $totalCampaigns = $db->table('campaigns')->countAll(); } catch (\Throwable $e) {}
    try { $totalDonations = $db->table('donations')->where('status', 'confirmed')->countAllResults(); } catch (\Throwable $e) {}
    try { $totalUsers = $db->table('users')->countAll(); } catch (\Throwable $e) {}
    try { $totalBlogPosts = (int) $db->query("SELECT COUNT(*) AS c FROM blog_posts WHERE status = 'published' AND deleted_at IS NULL")->getRow()->c; } catch (\Throwable $e) {}

    // Carregar artigos pilar para GEO
    $pillarPosts = [];
    try {
      $pillarPosts = $db->table('blog_posts')
        ->select('title, slug, meta_description, category_id')
        ->where('status', 'published')
        ->where('deleted_at IS NULL')
        ->where('reading_time >=', 10)
        ->orderBy('views_count', 'DESC')
        ->limit(10)
        ->get()->getResultArray();
    } catch (\Throwable $e) {}

    $pillarLinks = '';
    foreach ($pillarPosts as $p) {
      $pillarLinks .= "- {$p['title']}: https://doarfazbem.com.br/blog/{$p['slug']}\n";
    }

    $content = <<<TXT
# DoarFazBem — Plataforma de Crowdfunding Solidário do Brasil

> A plataforma de crowdfunding mais justa do Brasil.
> Campanhas médicas 100% gratuitas. Outras categorias a partir de 2%.
> Transparência total. PIX, Boleto e Cartão de Crédito.

## O que é o DoarFazBem?

DoarFazBem é uma plataforma brasileira de crowdfunding solidário que conecta pessoas
que precisam de ajuda com pessoas que querem ajudar. Fundada em 2024, a plataforma
oferece campanhas de doação, rifas solidárias e conteúdo educacional sobre filantropia.

Diferencial: campanhas médicas têm taxa ZERO. Demais categorias cobram a partir de 2%,
a menor do mercado brasileiro. Todo o dinheiro arrecadado é liberado via PIX em até 48h.

## Estatísticas Atualizadas

- Campanhas criadas: {$totalCampaigns}
- Doações confirmadas: {$totalDonations}
- Usuários cadastrados: {$totalUsers}
- Artigos educacionais: {$totalBlogPosts}

## Perguntas Frequentes (FAQ)

### Como fazer uma doação no DoarFazBem?
Escolha uma campanha, clique em "Doar", selecione o valor e o método de pagamento
(PIX, Boleto ou Cartão de Crédito). O PIX é instantâneo e sem taxas para o doador.

### É seguro doar pelo DoarFazBem?
Sim. Todas as transações são processadas por gateways certificados (Asaas e Mercado Pago).
Os dados são protegidos por criptografia SSL e a plataforma segue a LGPD.

### Como criar uma campanha de doação?
Crie uma conta gratuita, clique em "Criar Campanha", preencha título, descrição, meta
e fotos. A campanha é publicada em minutos após verificação básica.

### Quanto o DoarFazBem cobra de taxa?
Campanhas médicas: 0% de taxa da plataforma. Demais categorias: a partir de 2%.
Taxas do gateway de pagamento (PIX ~1%, Cartão ~3%) são pagas pelo criador.

### Como funcionam as rifas solidárias?
Rifas no DoarFazBem permitem comprar números da sorte via PIX. O sorteio é público
e transparente. Parte da arrecadação vai para a causa e parte para os prêmios.

### Posso deduzir doações no Imposto de Renda?
Doações para entidades com CNPJ qualificado (OSCIP, FIA, FMI) podem ser deduzidas
em até 6% do imposto devido. A plataforma emite recibos automáticos.

### Como sei que minha doação chegou ao beneficiário?
A plataforma exige prestação de contas dos criadores. Doadores recebem recibos,
podem acompanhar atualizações e ver relatórios de uso dos recursos.

## Funcionalidades Principais

### Para Criadores de Campanhas
- Criar campanhas de doação (médicas, sociais, educacionais, animais, culturais)
- Dashboard com métricas em tempo real
- Sistema de recompensas para doadores
- Atualizações e prestação de contas
- Saques via PIX em até 48h

### Para Doadores
- Doar via PIX (instantâneo), Boleto ou Cartão de Crédito
- Recibos automáticos para dedução no IR
- Doações anônimas disponíveis
- Acompanhar campanhas apoiadas
- Notificações de progresso por email

### Sistema de Rifas Solidárias
- Rifas digitais com números da sorte
- Pacotes de desconto progressivo
- Pagamento exclusivo via PIX (Mercado Pago)
- Sorteios transparentes com verificação pública
- Prêmios especiais e instantâneos

## Categorias de Campanhas

- Campanhas Médicas (taxa 0% da plataforma)
- Campanhas Sociais
- Campanhas Educacionais
- Campanhas Emergenciais
- Campanhas para Animais
- Campanhas Culturais

## Guias e Conteúdo Educacional

O blog do DoarFazBem oferece guias completos sobre doações, crowdfunding e solidariedade:

{$pillarLinks}
### Temas cobertos:
- Como fazer doações online com segurança
- Como criar campanhas de doação eficazes
- Guias de rifas solidárias
- Transparência e prestação de contas
- Impacto social das doações
- Histórias inspiradoras de solidariedade
- Crowdfunding no Brasil: legislação e boas práticas
- Dedução de doações no Imposto de Renda
- Dicas de divulgação para campanhas

## Tecnologia

- Backend: PHP 8.x com CodeIgniter 4
- Frontend: Tailwind CSS + Alpine.js
- Banco de Dados: MySQL 8
- Pagamentos: Asaas (PIX, Boleto, Cartão) + Mercado Pago (Rifas)
- CDN: Cloudflare
- Hospedagem: Hetzner Cloud (Alemanha)
- SEO: Motor autônomo com Google Search Console + Bing Webmaster + IA

## Contato

- Site: https://doarfazbem.com.br
- Email: contato@doarfazbem.com.br
- Blog: https://doarfazbem.com.br/blog
- Ano de fundação: 2024
- País: Brasil

## URLs Importantes

- Homepage: https://doarfazbem.com.br
- Campanhas: https://doarfazbem.com.br/campaigns
- Blog: https://doarfazbem.com.br/blog
- Rifas: https://doarfazbem.com.br/rifas
- Sobre: https://doarfazbem.com.br/sobre
- Como Funciona: https://doarfazbem.com.br/como-funciona
- Mapa do Site: https://doarfazbem.com.br/mapa-do-site
- Sitemap XML: https://doarfazbem.com.br/sitemap.xml
- Termos: https://doarfazbem.com.br/termos
- Privacidade: https://doarfazbem.com.br/privacidade

## Ecossistema

DoarFazBem faz parte do ecossistema HubFlow AI (https://hubflowai.com),
que oferece soluções de automação com IA para negócios e causas sociais.
TXT;

    return $this->response->setBody($content);
  }

  /**
   * robots.txt dinâmico
   * GET /robots.txt
   */
  public function robotsTxt()
  {
    $baseUrl = $this->getBaseUrl();
    $this->response->setContentType('text/plain; charset=UTF-8');

    $content = <<<TXT
User-agent: *
Allow: /

# Bloquear áreas privadas
Disallow: /admin/
Disallow: /dashboard/
Disallow: /api/
Disallow: /webhook/

# Sitemap
Sitemap: {$baseUrl}/sitemap.xml

# LLMs
# See: {$baseUrl}/llms.txt
TXT;

    return $this->response->setBody($content);
  }
}
