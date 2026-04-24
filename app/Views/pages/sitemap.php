<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
      <li><a href="<?= base_url() ?>" class="hover:text-emerald-600">Início</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li class="font-medium text-gray-700">Mapa do Site</li>
    </ol>
  </nav>

  <div class="text-center mb-10">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Mapa do Site</h1>
    <p class="text-gray-500">Navegue por todas as páginas do DoarFazBem</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <!-- Páginas Principais -->
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-home text-emerald-600 mr-2"></i>Páginas Principais
      </h2>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= base_url() ?>" class="text-gray-600 hover:text-emerald-600 transition">Página Inicial</a></li>
        <li><a href="<?= base_url('campaigns') ?>" class="text-gray-600 hover:text-emerald-600 transition">Campanhas</a></li>
        <li><a href="<?= base_url('rifas') ?>" class="text-gray-600 hover:text-emerald-600 transition">Rifas Solidárias</a></li>
        <li><a href="<?= base_url('blog') ?>" class="text-gray-600 hover:text-emerald-600 transition">Blog</a></li>
        <li><a href="<?= base_url('sobre') ?>" class="text-gray-600 hover:text-emerald-600 transition">Sobre Nós</a></li>
        <li><a href="<?= base_url('como-funciona') ?>" class="text-gray-600 hover:text-emerald-600 transition">Como Funciona</a></li>
        <li><a href="<?= base_url('termos') ?>" class="text-gray-600 hover:text-emerald-600 transition">Termos de Uso</a></li>
        <li><a href="<?= base_url('privacidade') ?>" class="text-gray-600 hover:text-emerald-600 transition">Política de Privacidade</a></li>
      </ul>
    </div>

    <!-- Conta -->
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-user text-blue-600 mr-2"></i>Sua Conta
      </h2>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= base_url('login') ?>" class="text-gray-600 hover:text-emerald-600 transition">Login</a></li>
        <li><a href="<?= base_url('register') ?>" class="text-gray-600 hover:text-emerald-600 transition">Criar Conta</a></li>
        <li><a href="<?= base_url('dashboard') ?>" class="text-gray-600 hover:text-emerald-600 transition">Dashboard</a></li>
        <li><a href="<?= base_url('campaigns/create') ?>" class="text-gray-600 hover:text-emerald-600 transition">Criar Campanha</a></li>
      </ul>
    </div>

    <!-- Recursos -->
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-cogs text-purple-600 mr-2"></i>Recursos
      </h2>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= base_url('sitemap.xml') ?>" class="text-gray-600 hover:text-emerald-600 transition">Sitemap XML</a></li>
        <li><a href="<?= base_url('blog/feed') ?>" class="text-gray-600 hover:text-emerald-600 transition">Feed RSS</a></li>
        <li><a href="<?= base_url('llms.txt') ?>" class="text-gray-600 hover:text-emerald-600 transition">llms.txt</a></li>
        <li><a href="<?= base_url('robots.txt') ?>" class="text-gray-600 hover:text-emerald-600 transition">robots.txt</a></li>
      </ul>
    </div>

    <!-- Categorias do Blog -->
    <?php if (!empty($categories)): ?>
    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-tags text-orange-500 mr-2"></i>Categorias do Blog
      </h2>
      <ul class="space-y-2 text-sm">
        <?php foreach ($categories as $cat): ?>
        <li><a href="<?= base_url('blog/categoria/' . $cat['slug']) ?>" class="text-gray-600 hover:text-emerald-600 transition"><?= esc($cat['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Artigos do Blog -->
    <?php if (!empty($blogPosts)): ?>
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:col-span-2">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-newspaper text-emerald-600 mr-2"></i>Artigos do Blog
      </h2>
      <ul class="space-y-2 text-sm columns-1 md:columns-2 gap-6">
        <?php foreach ($blogPosts as $post): ?>
        <li class="break-inside-avoid">
          <a href="<?= base_url('blog/' . $post['slug']) ?>" class="text-gray-600 hover:text-emerald-600 transition"><?= esc($post['title']) ?></a>
          <span class="text-xs text-gray-400 ml-1"><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Campanhas Ativas -->
    <?php if (!empty($campaigns)): ?>
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:col-span-2 lg:col-span-3">
      <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-hand-holding-heart text-red-500 mr-2"></i>Campanhas Ativas
      </h2>
      <ul class="space-y-2 text-sm columns-1 md:columns-2 lg:columns-3 gap-6">
        <?php foreach ($campaigns as $c): ?>
        <li class="break-inside-avoid">
          <a href="<?= base_url('campaigns/' . ($c['slug'] ?? $c['id'])) ?>" class="text-gray-600 hover:text-emerald-600 transition"><?= esc($c['title']) ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
