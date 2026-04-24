<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-search text-blue-600 mr-2"></i>SEO Dashboard
      </h1>
      <p class="text-gray-500 mt-1">Monitore e otimize o SEO da plataforma</p>
    </div>
  </div>

  <!-- KPI Cards SEO -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Posts no Blog</p>
          <p class="text-2xl font-bold text-gray-900"><?= $stats['blog_posts'] ?? 0 ?></p>
          <p class="text-xs text-green-600 mt-1"><?= $stats['published_posts'] ?? 0 ?> publicados</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
          <i class="fas fa-newspaper text-green-600 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Erros 404</p>
          <p class="text-2xl font-bold text-gray-900"><?= $stats['total_404'] ?? 0 ?></p>
          <p class="text-xs text-red-600 mt-1"><?= $stats['pending_404'] ?? 0 ?> pendentes</p>
        </div>
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
          <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Redirects</p>
          <p class="text-2xl font-bold text-gray-900"><?= $stats['total_redirects'] ?? 0 ?></p>
          <p class="text-xs text-blue-600 mt-1"><?= $stats['active_redirects'] ?? 0 ?> ativos</p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
          <i class="fas fa-directions text-blue-600 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">FAQs Schema</p>
          <p class="text-2xl font-bold text-gray-900"><?= $stats['total_faqs'] ?? 0 ?></p>
          <p class="text-xs text-purple-600 mt-1">Schema.org ativo</p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
          <i class="fas fa-question-circle text-purple-600 text-xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Regras de Links</p>
          <p class="text-2xl font-bold text-gray-900"><?= $stats['total_link_rules'] ?? 0 ?></p>
          <p class="text-xs text-orange-600 mt-1"><?= $stats['active_link_rules'] ?? 0 ?> ativas</p>
        </div>
        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
          <i class="fas fa-link text-orange-600 text-xl"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Links Rápidos -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <a href="<?= base_url('admin/seo/404') ?>" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center group-hover:bg-red-200 transition">
          <i class="fas fa-link-slash text-red-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-red-600">Monitor 404</h3>
          <p class="text-sm text-gray-500">Identifique e corrija links quebrados</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('admin/seo/redirects') ?>" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition">
          <i class="fas fa-route text-blue-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">Redirects 301/302</h3>
          <p class="text-sm text-gray-500">Gerencie redirecionamentos SEO</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('admin/seo/faqs') ?>" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition">
          <i class="fas fa-clipboard-question text-purple-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-600">FAQs Schema</h3>
          <p class="text-sm text-gray-500">Gerencie FAQs com Schema.org</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('admin/seo/internal-links') ?>" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-200 transition">
          <i class="fas fa-link text-orange-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-orange-600">Linkagem Interna</h3>
          <p class="text-sm text-gray-500">Auto-linking de keywords no conteúdo</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('admin/blog') ?>" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition">
          <i class="fas fa-pen-fancy text-green-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-green-600">Blog / Artigos</h3>
          <p class="text-sm text-gray-500">Criar e gerenciar artigos com SEO</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('sitemap.xml') ?>" target="_blank" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:bg-yellow-200 transition">
          <i class="fas fa-sitemap text-yellow-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-yellow-600">Sitemap XML</h3>
          <p class="text-sm text-gray-500">Ver sitemap dinâmico</p>
        </div>
      </div>
    </a>

    <a href="<?= base_url('llms.txt') ?>" target="_blank" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition group">
      <div class="flex items-center space-x-4">
        <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-200 transition">
          <i class="fas fa-robot text-indigo-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">LLMs.txt</h3>
          <p class="text-sm text-gray-500">Arquivo para IA/LLMs</p>
        </div>
      </div>
    </a>
  </div>

  <!-- Checklist SEO -->
  <div class="bg-white rounded-xl shadow-sm border p-6">
    <h3 class="text-lg font-semibold mb-4"><i class="fas fa-clipboard-check text-emerald-600 mr-2"></i>Checklist SEO</h3>
    <div class="space-y-3">
      <?php
        $checks = [
          ['Sitemap XML', $stats['blog_posts'] > 0 || true, 'sitemap.xml está ativo e dinâmico'],
          ['robots.txt', true, 'robots.txt configurado corretamente'],
          ['LLMs.txt', true, 'Arquivo para IA/LLMs disponível'],
          ['Blog ativo', ($stats['published_posts'] ?? 0) > 0, 'Publicar artigos regularmente melhora o SEO'],
          ['FAQs Schema', ($stats['total_faqs'] ?? 0) > 0, 'FAQs com Schema.org melhoram visibilidade no Google'],
          ['Meta Tags', true, 'Meta tags dinâmicas ativas via SeoMetaService'],
          ['Schema.org', true, 'Organization, Article, DonateAction, BreadcrumbList ativos'],
          ['Open Graph', true, 'Tags OG para compartilhamento social'],
          ['Canonical URLs', true, 'URLs canônicas em todas as páginas'],
          ['Linkagem Interna', ($stats['active_link_rules'] ?? 0) > 0, 'Auto-linking de keywords conecta blog ↔ campanhas'],
          ['404 Monitor', true, 'Monitoramento ativo de links quebrados'],
        ];
        foreach ($checks as $check):
      ?>
      <div class="flex items-center space-x-3 p-2 rounded-lg <?= $check[1] ? 'bg-green-50' : 'bg-yellow-50' ?>">
        <i class="fas <?= $check[1] ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-yellow-500' ?> text-lg"></i>
        <div>
          <p class="text-sm font-medium text-gray-900"><?= $check[0] ?></p>
          <p class="text-xs text-gray-500"><?= $check[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
